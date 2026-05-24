import express from 'express';
import { getUnifiedChains, getWalletBalance, processTransaction, getDepositAddress, verifyDeposit } from '../controllers/wallet.controller';
import { authenticateToken } from '../middleware/auth';

const router = express.Router();

// All wallet routes should be authenticated
router.use(authenticateToken);

router.get('/unified-chains', getUnifiedChains);
router.get('/balance', getWalletBalance);
router.post('/transaction', processTransaction);

router.get('/deposit-address', getDepositAddress);
router.post('/verify-deposit', verifyDeposit);

export default router;
