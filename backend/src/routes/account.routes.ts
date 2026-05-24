import express from 'express';
import { getAccountDetails } from '../controllers/account.controller';
import { authenticateToken } from '../middleware/auth';

const router = express.Router();
router.use(authenticateToken);

router.get('/details', getAccountDetails);

export default router;
