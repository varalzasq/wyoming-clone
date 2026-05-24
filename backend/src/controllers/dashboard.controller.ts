import { Response } from 'express';
import { PrismaClient } from '@prisma/client';
import { AuthenticatedRequest } from '../middleware/auth';

const prisma = new PrismaClient();

export const getUserProfile = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) return res.status(401).json({ success: false, message: 'Unauthorized' });

    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        firstName: true,
        lastName: true,
        email: true,
        phone: true,
        address: true,
        city: true,
        state: true,
        zip: true,
        createdAt: true
      }
    });

    if (!user) {
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    res.status(200).json({ success: true, data: user });
  } catch (error) {
    console.error('Error fetching user profile:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const getLlcStats = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) return res.status(401).json({ success: false, message: 'Unauthorized' });

    const llcs = await prisma.llcRegistration.findMany({
      where: { userId }
    });

    const stats = {
      total: llcs.length,
      approved: llcs.filter(llc => llc.status === 'APPROVED').length,
      pending: llcs.filter(llc => llc.status === 'PENDING_PAYMENT').length,
      rejected: llcs.filter(llc => llc.status === 'REJECTED').length,
      processing: llcs.filter(llc => llc.status === 'PROCESSING').length,
    };

    res.status(200).json({ success: true, data: stats });
  } catch (error) {
    console.error('Error fetching LLC stats:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};

export const getLlcList = async (req: AuthenticatedRequest, res: Response) => {
  try {
    const userId = req.user?.id;
    if (!userId) return res.status(401).json({ success: false, message: 'Unauthorized' });

    const llcs = await prisma.llcRegistration.findMany({
      where: { userId },
      orderBy: { createdAt: 'desc' }
    });

    // Formatting date and mapping fields to match frontend expectations
    const formattedLlcs = llcs.map(llc => ({
      id: llc.id,
      companyName: llc.companyName,
      entityType: llc.designator,
      state: llc.state,
      status: llc.status,
      date: llc.createdAt.toISOString().split('T')[0], // YYYY-MM-DD
      stateFee: 100, // Static for Wyoming
      cryptoProtectionActive: llc.cryptoProtectionActive,
      roiTrackingStatus: llc.roiTrackingStatus
    }));

    res.status(200).json({ success: true, data: formattedLlcs });
  } catch (error) {
    console.error('Error fetching LLC list:', error);
    res.status(500).json({ success: false, message: 'Internal server error' });
  }
};
