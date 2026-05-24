"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getSwapQuote = exports.processSwap = void 0;
const client_1 = require("@prisma/client");
const prisma = new client_1.PrismaClient();
const processSwap = async (req, res) => {
    try {
        const userId = req.user?.id;
        const { fromSymbol, toSymbol, fromAmount } = req.body;
        if (!userId) {
            return res.status(401).json({ success: false, message: 'Unauthorized' });
        }
        if (!fromSymbol || !toSymbol || !fromAmount) {
            return res.status(400).json({
                success: false,
                message: 'Missing required fields: fromSymbol, toSymbol, fromAmount',
            });
        }
        if (fromSymbol === toSymbol) {
            return res.status(400).json({ success: false, message: 'Cannot swap an asset for itself' });
        }
        const parsedAmount = parseFloat(fromAmount);
        if (isNaN(parsedAmount) || parsedAmount <= 0) {
            return res.status(400).json({ success: false, message: 'Invalid amount' });
        }
        // Fetch both assets
        const [fromAsset, toAsset] = await Promise.all([
            prisma.walletAsset.findUnique({ where: { symbol: fromSymbol.toUpperCase() } }),
            prisma.walletAsset.findUnique({ where: { symbol: toSymbol.toUpperCase() } }),
        ]);
        if (!fromAsset) {
            return res.status(404).json({ success: false, message: `Asset ${fromSymbol} not found` });
        }
        if (!toAsset) {
            return res.status(404).json({ success: false, message: `Asset ${toSymbol} not found` });
        }
        // Calculate output amount using prices
        const fromUsdValue = parsedAmount * fromAsset.price;
        const toAmount = fromUsdValue / toAsset.price;
        // Apply a 0.3% swap fee (deducted from output)
        const FEE_RATE = 0.003;
        const feeAmount = toAmount * FEE_RATE;
        const netToAmount = toAmount - feeAmount;
        // Check user has sufficient fromAsset balance
        const fromBalance = await prisma.virtualBalance.findUnique({
            where: { userId_assetId: { userId, assetId: fromAsset.id } },
        });
        if (!fromBalance || fromBalance.amount < parsedAmount) {
            return res.status(400).json({
                success: false,
                message: `Insufficient ${fromSymbol} balance`,
            });
        }
        // Atomic ledger swap
        const result = await prisma.$transaction(async (tx) => {
            // Deduct fromAsset
            const updatedFrom = await tx.virtualBalance.update({
                where: { userId_assetId: { userId, assetId: fromAsset.id } },
                data: { amount: fromBalance.amount - parsedAmount },
            });
            // Credit toAsset (upsert in case user doesn't have it yet)
            const updatedTo = await tx.virtualBalance.upsert({
                where: { userId_assetId: { userId, assetId: toAsset.id } },
                update: { amount: { increment: netToAmount } },
                create: { userId, assetId: toAsset.id, amount: netToAmount },
            });
            // Record swap transactions
            const swapOut = await tx.transaction.create({
                data: {
                    userId,
                    type: 'SWAP',
                    assetSymbol: fromAsset.symbol,
                    amount: parsedAmount,
                    status: 'COMPLETED',
                },
            });
            const swapIn = await tx.transaction.create({
                data: {
                    userId,
                    type: 'SWAP',
                    assetSymbol: toAsset.symbol,
                    amount: netToAmount,
                    status: 'COMPLETED',
                },
            });
            return { updatedFrom, updatedTo, swapOut, swapIn };
        });
        return res.status(200).json({
            success: true,
            message: 'Swap completed successfully',
            data: {
                fromSymbol,
                toSymbol,
                fromAmount: parsedAmount,
                toAmount: netToAmount,
                feeAmount,
                exchangeRate: toAsset.price / fromAsset.price,
                fromUsdValue,
            },
        });
    }
    catch (error) {
        console.error('Error processing swap:', error);
        return res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.processSwap = processSwap;
const getSwapQuote = async (req, res) => {
    try {
        const { fromSymbol, toSymbol, fromAmount } = req.query;
        if (!fromSymbol || !toSymbol || !fromAmount) {
            return res.status(400).json({ success: false, message: 'Missing query parameters' });
        }
        const [fromAsset, toAsset] = await Promise.all([
            prisma.walletAsset.findUnique({ where: { symbol: fromSymbol.toUpperCase() } }),
            prisma.walletAsset.findUnique({ where: { symbol: toSymbol.toUpperCase() } }),
        ]);
        if (!fromAsset || !toAsset) {
            return res.status(404).json({ success: false, message: 'Asset not found' });
        }
        const parsed = parseFloat(fromAmount);
        const fromUsdValue = parsed * fromAsset.price;
        const rawToAmount = fromUsdValue / toAsset.price;
        const feeAmount = rawToAmount * 0.003;
        const netToAmount = rawToAmount - feeAmount;
        return res.status(200).json({
            success: true,
            data: {
                fromSymbol: fromAsset.symbol,
                toSymbol: toAsset.symbol,
                fromAmount: parsed,
                toAmount: netToAmount,
                exchangeRate: toAsset.price / fromAsset.price,
                feeRate: '0.3%',
                fromUsdValue,
            },
        });
    }
    catch (error) {
        return res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.getSwapQuote = getSwapQuote;
