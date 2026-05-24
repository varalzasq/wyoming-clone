import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { getHotWalletAddress, getHotWalletBalances } from '../utils/web3';

const prisma = new PrismaClient();

export const getHotWalletStats = async (req: Request, res: Response) => {
  try {
    const address = getHotWalletAddress();
    
    if (!address) {
      return res.status(200).json({
        success: true,
        address: null,
        balances: null,
        message: 'No hot wallet configured'
      });
    }

    const balances = await getHotWalletBalances();

    res.status(200).json({
      success: true,
      address,
      balances,
    });
  } catch (error) {
    console.error('Error in getHotWalletStats:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const getPlatformLiability = async (req: Request, res: Response) => {
  try {
    // Sum all user virtual balances grouped by asset
    const allBalances = await prisma.virtualBalance.findMany({
      include: { asset: true }
    });

    const liabilities: Record<string, number> = {};
    
    allBalances.forEach(b => {
      if (!liabilities[b.asset.symbol]) {
        liabilities[b.asset.symbol] = 0;
      }
      liabilities[b.asset.symbol] += b.amount;
    });

    res.status(200).json({
      success: true,
      liabilities,
    });
  } catch (error) {
    console.error('Error in getPlatformLiability:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
