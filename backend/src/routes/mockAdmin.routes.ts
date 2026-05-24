import { Router } from 'express';
import { mockAdminLogin, mockAdminVerify } from '../controllers/mockAdmin.controller';

const router = Router();

router.post('/login', mockAdminLogin);
router.post('/verify', mockAdminVerify);

export default router;
