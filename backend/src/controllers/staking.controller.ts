import { Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { AuthenticatedRequest } from '../middleware/auth';

const prisma = new PrismaClient();

const ANNUAL_ROI = 0.20; // 20% APY
const DAYS_IN_YEAR = 365;

export const getStakingYield = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) {
      return res.status(401).json({ success: false, message: 'Unauthorized' });
    }

    // Get user creation date for accrual calculation
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: { createdAt: true },
    });
    if (!user) {
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    const daysSinceJoin = Math.max(
      1,
      Math.floor((Date.now() - user.createdAt.getTime()) / (1000 * 60 * 60 * 24))
    );

    // Get all virtual balances with asset info
    const balances = await prisma.virtualBalance.findMany({
      where: { userId },
      include: { asset: true },
    });

    let totalUsd = 0;
    const assetBreakdown: Array<{
      symbol: string;
      name: string;
      iconColor: string;
      iconUrl: string;
      stakedUsd: number;
      accruedUsd: number;
      dailyYieldUsd: number;
      apy: number;
    }> = [];

    for (const b of balances) {
      const usdValue = b.amount * b.asset.price;
      const dailyYield = (usdValue * ANNUAL_ROI) / DAYS_IN_YEAR;
      const accruedYield = dailyYield * daysSinceJoin;
      totalUsd += usdValue;

      assetBreakdown.push({
        symbol: b.asset.symbol,
        name: b.asset.name,
        iconColor: b.asset.iconColor,
        iconUrl: b.asset.iconUrl,
        stakedUsd: usdValue,
        accruedUsd: accruedYield,
        dailyYieldUsd: dailyYield,
        apy: ANNUAL_ROI * 100,
      });
    }

    const totalDailyYield = (totalUsd * ANNUAL_ROI) / DAYS_IN_YEAR;
    const totalAccruedYield = totalDailyYield * daysSinceJoin;
    const projectedAnnualYield = totalUsd * ANNUAL_ROI;

    return res.status(200).json({
      success: true,
      data: {
        totalStakedUsd: totalUsd,
        totalAccruedUsd: totalAccruedYield,
        totalDailyYieldUsd: totalDailyYield,
        projectedAnnualYieldUsd: projectedAnnualYield,
        apy: ANNUAL_ROI * 100,
        daysSinceJoin,
        assetBreakdown,
      },
    });
  } catch (error) {
    console.error('Error fetching staking yield:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
