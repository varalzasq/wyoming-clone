import express from 'express';
import { sendFromTreasury, estimateGas } from '../controllers/send.controller';
import { authenticateToken } from '../middleware/auth';

const router = express.Router();
router.use(authenticateToken);

router.get('/gas-estimate', estimateGas);
router.post('/send', sendFromTreasury);

export default router;
