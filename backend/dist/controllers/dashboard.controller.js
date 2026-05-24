"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getLlcList = exports.getLlcStats = exports.getUserProfile = void 0;
const client_1 = require("@prisma/client");
const prisma = new client_1.PrismaClient();
const getUserProfile = async (req, res) => {
    try {
        const userId = req.user?.id;
        if (!userId)
            return res.status(401).json({ success: false, message: 'Unauthorized' });
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
    }
    catch (error) {
        console.error('Error fetching user profile:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getUserProfile = getUserProfile;
const getLlcStats = async (req, res) => {
    try {
        const userId = req.user?.id;
        if (!userId)
            return res.status(401).json({ success: false, message: 'Unauthorized' });
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
    }
    catch (error) {
        console.error('Error fetching LLC stats:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getLlcStats = getLlcStats;
const getLlcList = async (req, res) => {
    try {
        const userId = req.user?.id;
        if (!userId)
            return res.status(401).json({ success: false, message: 'Unauthorized' });
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
    }
    catch (error) {
        console.error('Error fetching LLC list:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getLlcList = getLlcList;
