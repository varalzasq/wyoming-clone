import { Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { ethers } from 'ethers';
import { AuthenticatedRequest } from '../middleware/auth';

const prisma = new PrismaClient();

// Static gas estimate per asset network (USD)
const GAS_ESTIMATES: Record<string, number> = {
  ETH: 2.5,
  USDC: 3.0,
  USDT: 3.0,
  WBTC: 2.8,
  BNB: 0.15,
  MATIC: 0.05,
  SOL: 0.001,
  AVAX: 0.1,
};

export const estimateGas = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const { assetSymbol } = req.query as { assetSymbol: string };
    const gasUsd = GAS_ESTIMATES[assetSymbol?.toUpperCase()] ?? 2.5;
    return res.status(200).json({ success: true, gasUsd });
  } catch (error) {
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const sendFromTreasury = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    const { assetSymbol, toAddress, amount } = req.body;

    // ── Input validation ──
    if (!userId) {
      return res.status(401).json({ success: false, message: 'Unauthorized' });
    }
    if (!assetSymbol || !toAddress || !amount) {
      return res.status(400).json({
        success: false,
        message: 'Missing required fields: assetSymbol, toAddress, amount',
      });
    }
    if (!ethers.isAddress(toAddress)) {
      return res.status(400).json({ success: false, message: 'Invalid destination address' });
    }
    const parsedAmount = parseFloat(amount);
    if (isNaN(parsedAmount) || parsedAmount <= 0) {
      return res.status(400).json({ success: false, message: 'Invalid amount' });
    }

    // ── Check Treasury config ──
    const treasuryKey = process.env.TREASURY_PRIVATE_KEY;
    const rpcUrl = process.env.RPC_URL;
    if (!treasuryKey || !rpcUrl) {
      return res.status(503).json({
        success: false,
        message: 'Treasury not configured. Please contact support.',
      });
    }

    // ── Fetch asset ──
    const asset = await prisma.walletAsset.findUnique({
      where: { symbol: assetSymbol.toUpperCase() },
    });
    if (!asset) {
      return res.status(404).json({ success: false, message: 'Asset not found' });
    }

    // ── Check virtual balance ──
    const virtualBalance = await prisma.virtualBalance.findUnique({
      where: { userId_assetId: { userId, assetId: asset.id } },
    });
    const gasUsd = GAS_ESTIMATES[assetSymbol.toUpperCase()] ?? 2.5;
    const gasInAsset = gasUsd / asset.price;
    const totalRequired = parsedAmount + gasInAsset;

    if (!virtualBalance || virtualBalance.amount < totalRequired) {
      return res.status(400).json({
        success: false,
        message: `Insufficient balance. Required: ${totalRequired.toFixed(8)} ${assetSymbol} (incl. gas)`,
      });
    }

    // ── Deduct from ledger (PENDING) ──
    const txRecord = await prisma.$transaction(async (tx) => {
      // Create transaction record
      const transaction = await tx.transaction.create({
        data: {
          userId,
          type: 'WITHDRAWAL',
          assetSymbol: asset.symbol,
          amount: parsedAmount,
          status: 'PENDING',
        },
      });

      // Deduct balance
      await tx.virtualBalance.update({
        where: { userId_assetId: { userId, assetId: asset.id } },
        data: { amount: virtualBalance.amount - totalRequired },
      });

      return transaction;
    });

    // ── Broadcast from Treasury ──
    try {
      const provider = new ethers.JsonRpcProvider(rpcUrl);
      const wallet = new ethers.Wallet(treasuryKey, provider);

      // For ETH native transfers
      const txResponse = await wallet.sendTransaction({
        to: toAddress,
        value: ethers.parseEther(parsedAmount.toString()),
      });

      // Update transaction record with hash
      await prisma.transaction.update({
        where: { id: txRecord.id },
        data: { txHash: txResponse.hash, status: 'COMPLETED' },
      });

      return res.status(200).json({
        success: true,
        message: 'Withdrawal broadcast successfully',
        data: {
          txHash: txResponse.hash,
          amount: parsedAmount,
          gasDeducted: gasInAsset,
          asset: assetSymbol,
          to: toAddress,
        },
      });
    } catch (broadcastError: any) {
      // Refund if broadcast fails
      await prisma.$transaction(async (tx) => {
        await tx.virtualBalance.update({
          where: { userId_assetId: { userId, assetId: asset.id } },
          data: { amount: virtualBalance.amount }, // restore original
        });
        await tx.transaction.update({
          where: { id: txRecord.id },
          data: { status: 'FAILED' },
        });
      });

      console.error('Treasury broadcast failed:', broadcastError);
      return res.status(502).json({
        success: false,
        message: 'Broadcast failed. Your balance has been restored. Please try again.',
      });
    }
  } catch (error: any) {
    console.error('Error in sendFromTreasury:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
