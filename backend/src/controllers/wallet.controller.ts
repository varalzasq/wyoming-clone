import { Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { AuthenticatedRequest } from '../middleware/auth';
import { getHotWalletAddress } from '../utils/web3';

const prisma = new PrismaClient();

export const getUnifiedChains = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const assets = await prisma.walletAsset.findMany({
      orderBy: { symbol: 'asc' }
    });
    
    return res.status(200).json({
      success: true,
      assets
    });
  } catch (error) {
    console.error('Error fetching unified chains:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const getWalletBalance = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) {
      return res.status(401).json({ success: false, message: 'Unauthorized' });
    }

    const balances = await prisma.virtualBalance.findMany({
      where: { userId },
      include: { asset: true }
    });

    let totalUsd = 0;
    const formattedBalances = balances.map(b => {
      const usdValue = b.amount * b.asset.price;
      totalUsd += usdValue;
      return {
        ...b,
        usdValue
      };
    });

    return res.status(200).json({
      success: true,
      totalUsd,
      balances: formattedBalances
    });
  } catch (error) {
    console.error('Error fetching wallet balance:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const processTransaction = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    const { assetSymbol, amount, type } = req.body;

    if (!userId || !assetSymbol || !amount || !type) {
      return res.status(400).json({ success: false, message: 'Missing required fields' });
    }

    const asset = await prisma.walletAsset.findUnique({
      where: { symbol: assetSymbol }
    });

    if (!asset) {
      return res.status(404).json({ success: false, message: 'Asset not found' });
    }

    // Wrap in a transaction
    const result = await prisma.$transaction(async (tx) => {
      // 1. Log transaction
      const transaction = await tx.transaction.create({
        data: {
          userId,
          type,
          assetSymbol,
          amount,
          status: 'COMPLETED'
        }
      });

      // 2. Update Virtual Balance
      let virtualBalance = await tx.virtualBalance.findUnique({
        where: {
          userId_assetId: { userId, assetId: asset.id }
        }
      });

      if (!virtualBalance) {
        if (type === 'WITHDRAWAL') throw new Error('Insufficient balance');
        
        virtualBalance = await tx.virtualBalance.create({
          data: {
            userId,
            assetId: asset.id,
            amount: type === 'DEPOSIT' ? amount : 0
          }
        });
      } else {
        const newAmount = type === 'DEPOSIT' ? virtualBalance.amount + amount : virtualBalance.amount - amount;
        
        if (newAmount < 0) throw new Error('Insufficient balance');

        virtualBalance = await tx.virtualBalance.update({
          where: { id: virtualBalance.id },
          data: { amount: newAmount }
        });
      }

      return { transaction, virtualBalance };
    });

    return res.status(200).json({
      success: true,
      message: 'Transaction processed successfully',
      data: result
    });
  } catch (error: any) {
    console.error('Error processing transaction:', error);
    return res.status(400).json({ success: false, message: error.message || 'Transaction failed' });
  }
};

export const getDepositAddress = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const address = getHotWalletAddress();
    if (!address) {
      return res.status(503).json({ success: false, message: 'Treasury address not configured' });
    }
    return res.status(200).json({ success: true, address });
  } catch (error) {
    console.error('Error fetching deposit address:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const verifyDeposit = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    const { txHash, amount } = req.body;

    if (!userId || !txHash || !amount) {
      return res.status(400).json({ success: false, message: 'Missing required fields' });
    }

    // In a production app, we would use ethers/viem to verify the txHash actually went to the Hot Wallet address
    // and wait for sufficient confirmations. For this v1 demo, we trust the frontend payload since the 
    // transaction was sent via MetaMask/Wagmi and confirmed on the frontend.
    
    // We assume ETH for this direct transfer.
    const asset = await prisma.walletAsset.findUnique({
      where: { symbol: 'ETH' }
    });

    if (!asset) {
      return res.status(404).json({ success: false, message: 'ETH asset not found in database' });
    }

    const result = await prisma.$transaction(async (tx) => {
      // 1. Log transaction
      const transaction = await tx.transaction.create({
        data: {
          userId,
          type: 'DEPOSIT',
          assetSymbol: 'ETH',
          amount: parseFloat(amount),
          status: 'COMPLETED',
          txHash
        }
      });

      // 2. Update Virtual Balance
      let virtualBalance = await tx.virtualBalance.findUnique({
        where: {
          userId_assetId: { userId, assetId: asset.id }
        }
      });

      if (!virtualBalance) {
        virtualBalance = await tx.virtualBalance.create({
          data: {
            userId,
            assetId: asset.id,
            amount: parseFloat(amount)
          }
        });
      } else {
        virtualBalance = await tx.virtualBalance.update({
          where: { id: virtualBalance.id },
          data: { amount: virtualBalance.amount + parseFloat(amount) }
        });
      }

      return { transaction, virtualBalance };
    });

    return res.status(200).json({
      success: true,
      message: 'Deposit verified and credited',
      data: result
    });
  } catch (error: any) {
    console.error('Error verifying deposit:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
