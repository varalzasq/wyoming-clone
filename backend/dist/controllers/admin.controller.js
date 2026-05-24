"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getPlatformLiability = exports.getHotWalletStats = void 0;
const client_1 = require("@prisma/client");
const web3_1 = require("../utils/web3");
const prisma = new client_1.PrismaClient();
const getHotWalletStats = async (req, res) => {
    try {
        const address = (0, web3_1.getHotWalletAddress)();
        if (!address) {
            return res.status(200).json({
                success: true,
                address: null,
                balances: null,
                message: 'No hot wallet configured'
            });
        }
        const balances = await (0, web3_1.getHotWalletBalances)();
        res.status(200).json({
            success: true,
            address,
            balances,
        });
    }
    catch (error) {
        console.error('Error in getHotWalletStats:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getHotWalletStats = getHotWalletStats;
const getPlatformLiability = async (req, res) => {
    try {
        // Sum all user virtual balances grouped by asset
        const allBalances = await prisma.virtualBalance.findMany({
            include: { asset: true }
        });
        const liabilities = {};
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
    }
    catch (error) {
        console.error('Error in getPlatformLiability:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getPlatformLiability = getPlatformLiability;
