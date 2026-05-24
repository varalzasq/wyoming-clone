"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const wallet_controller_1 = require("../controllers/wallet.controller");
const auth_1 = require("../middleware/auth");
const router = express_1.default.Router();
// All wallet routes should be authenticated
router.use(auth_1.authenticateToken);
router.get('/unified-chains', wallet_controller_1.getUnifiedChains);
router.get('/balance', wallet_controller_1.getWalletBalance);
router.post('/transaction', wallet_controller_1.processTransaction);
router.get('/deposit-address', wallet_controller_1.getDepositAddress);
router.post('/verify-deposit', wallet_controller_1.verifyDeposit);
exports.default = router;
