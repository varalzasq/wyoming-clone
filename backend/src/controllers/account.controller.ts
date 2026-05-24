import { Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { AuthenticatedRequest } from '../middleware/auth';

const prisma = new PrismaClient();

export const getAccountDetails = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) {
      return res.status(401).json({ success: false, message: 'Unauthorized' });
    }

    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        email: true,
        firstName: true,
        lastName: true,
        phone: true,
        address: true,
        city: true,
        state: true,
        zip: true,
        createdAt: true,
        llcOrders: {
          select: {
            id: true,
            companyName: true,
            status: true,
            cryptoProtectionActive: true,
            createdAt: true,
          },
          orderBy: { createdAt: 'desc' },
        },
      },
    });

    if (!user) {
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    // Security settings (placeholder — real 2FA would use a separate table)
    const securitySettings = {
      twoFactorEnabled: false,
      pinEnabled: false,
      lastLoginAt: new Date().toISOString(),
      sessionActive: true,
    };

    return res.status(200).json({
      success: true,
      data: {
        profile: {
          id: user.id,
          email: user.email,
          firstName: user.firstName,
          lastName: user.lastName,
          phone: user.phone,
          address: user.address,
          city: user.city,
          state: user.state,
          zip: user.zip,
          memberSince: user.createdAt,
        },
        llcs: user.llcOrders,
        llcCount: user.llcOrders.length,
        securitySettings,
      },
    });
  } catch (error) {
    console.error('Error fetching account details:', error);
    return res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
