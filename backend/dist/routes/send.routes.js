"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const send_controller_1 = require("../controllers/send.controller");
const auth_1 = require("../middleware/auth");
const router = express_1.default.Router();
router.use(auth_1.authenticateToken);
router.get('/gas-estimate', send_controller_1.estimateGas);
router.post('/send', send_controller_1.sendFromTreasury);
exports.default = router;
