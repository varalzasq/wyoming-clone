import { Request, Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { z } from 'zod';

const prisma = new PrismaClient();

export const getAllUsers = async (req: Request, res: Response) => {
  try {
    const users = await prisma.user.findMany({
      select: {
        id: true,
        firstName: true,
        lastName: true,
        email: true,
        role: true,
        createdAt: true,
        llcOrders: {
          select: {
            id: true,
            companyName: true,
            status: true,
          }
        },
        balances: {
          select: {
            id: true,
            amount: true,
            asset: {
              select: {
                symbol: true,
                name: true,
              }
            }
          }
        }
      },
      orderBy: { createdAt: 'desc' }
    });

    res.status(200).json({ success: true, users });
  } catch (error) {
    console.error('Error in getAllUsers:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

const updateBalanceSchema = z.object({
  userId: z.string(),
  assetSymbol: z.string(),
  amountChange: z.number(), // Positive to add, negative to deduct
});

export const updateUserBalance = async (req: Request, res: Response) => {
  try {
    const validatedData = updateBalanceSchema.parse(req.body);
    const { userId, assetSymbol, amountChange } = validatedData;

    // Find the asset
    const asset = await prisma.walletAsset.findUnique({
      where: { symbol: assetSymbol }
    });

    if (!asset) {
      return res.status(404).json({ success: false, message: 'Asset not found' });
    }

    // Find or create the user's virtual balance for this asset
    let userBalance = await prisma.virtualBalance.findUnique({
      where: {
        userId_assetId: {
          userId,
          assetId: asset.id,
        }
      }
    });

    if (userBalance) {
      // Update existing
      const newAmount = Math.max(0, userBalance.amount + amountChange); // Prevent negative balance
      userBalance = await prisma.virtualBalance.update({
        where: { id: userBalance.id },
        data: { amount: newAmount }
      });
    } else {
      // Create new (if amountChange is positive)
      if (amountChange < 0) {
        return res.status(400).json({ success: false, message: 'Cannot deduct from a zero balance' });
      }
      userBalance = await prisma.virtualBalance.create({
        data: {
          userId,
          assetId: asset.id,
          amount: amountChange,
        }
      });
    }

    // Log the transaction
    await prisma.transaction.create({
      data: {
        userId,
        type: amountChange >= 0 ? 'ADMIN_CREDIT' : 'ADMIN_DEBIT',
        assetSymbol: asset.symbol,
        amount: Math.abs(amountChange),
        status: 'COMPLETED',
      }
    });

    res.status(200).json({
      success: true,
      message: 'Balance updated successfully',
      balance: userBalance
    });

  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        errors: (error as any).issues.map((err: any) => ({ field: err.path.join('.'), message: err.message })),
      });
    }
    console.error('Error in updateUserBalance:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
