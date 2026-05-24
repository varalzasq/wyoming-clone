"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = require("express");
const mockAdmin_controller_1 = require("../controllers/mockAdmin.controller");
const router = (0, express_1.Router)();
router.post('/login', mockAdmin_controller_1.mockAdminLogin);
router.post('/verify', mockAdmin_controller_1.mockAdminVerify);
exports.default = router;
