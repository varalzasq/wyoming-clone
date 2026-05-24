"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.dispatchWallet = exports.contactUs = exports.registerLlc = void 0;
const zod_1 = require("zod");
const client_1 = require("@prisma/client");
const bcryptjs_1 = __importDefault(require("bcryptjs"));
const nodemailer_1 = __importDefault(require("nodemailer"));
const jsonwebtoken_1 = __importDefault(require("jsonwebtoken"));
const JWT_SECRET = process.env.JWT_SECRET || 'fallback-secret-key-for-dev';
const prisma = new client_1.PrismaClient();
// Nodemailer Transporter Setup (Mocked for MVP)
const createTestTransporter = async () => {
    // Generate test SMTP service account from ethereal.email
    // Only needed if you don't have a real mail account for testing
    let testAccount = await nodemailer_1.default.createTestAccount();
    return nodemailer_1.default.createTransport({
        host: "smtp.ethereal.email",
        port: 587,
        secure: false, // true for 465, false for other ports
        auth: {
            user: testAccount.user, // generated ethereal user
            pass: testAccount.pass, // generated ethereal password
        },
    });
};
const registerLlcSchema = zod_1.z.object({
    personalInfo: zod_1.z.object({
        firstName: zod_1.z.string().min(1, 'First name is required'),
        lastName: zod_1.z.string().min(1, 'Last name is required'),
        email: zod_1.z.string().email('Invalid email format'),
        phone: zod_1.z.string().min(1, 'Phone number is required'),
        password: zod_1.z.string().min(6, 'Password must be at least 6 characters'),
        confirmPassword: zod_1.z.string().min(6, 'Confirm Password must be at least 6 characters'),
        // Contact Address Fields
        address: zod_1.z.string().min(1, 'Address is required'),
        city: zod_1.z.string().min(1, 'City is required'),
        state: zod_1.z.string().min(1, 'State is required'),
        zip: zod_1.z.string().min(1, 'ZIP Code is required'),
        partnerCode: zod_1.z.string().optional(),
    }).refine((data) => data.password === data.confirmPassword, {
        message: "Passwords don't match",
        path: ["confirmPassword"],
    }),
    companyDetails: zod_1.z.object({
        companyName: zod_1.z.string().min(1, 'Company name is required'),
        designator: zod_1.z.string().min(1, 'Designator is required'),
        state: zod_1.z.string().min(1, 'State is required'),
        industry: zod_1.z.string().min(1, 'Industry is required'),
    }),
    services: zod_1.z.object({
        registeredAgent: zod_1.z.boolean(),
        mailForwarding: zod_1.z.boolean(),
        einApplication: zod_1.z.boolean(),
        // New Crypto Package Fields
        cryptoProtectionActive: zod_1.z.boolean().optional().default(false),
        walletShippingAddress: zod_1.z.string().optional(),
    }),
});
const contactSchema = zod_1.z.object({
    name: zod_1.z.string().min(1, 'Name is required'),
    email: zod_1.z.string().email('Invalid email format'),
    subject: zod_1.z.string().min(1, 'Subject is required'),
    message: zod_1.z.string().min(1, 'Message is required'),
});
const registerLlc = async (req, res) => {
    try {
        const validatedData = registerLlcSchema.parse(req.body);
        // Check if user already exists
        const existingUser = await prisma.user.findUnique({
            where: { email: validatedData.personalInfo.email }
        });
        if (existingUser) {
            return res.status(400).json({ success: false, message: 'User with this email already exists' });
        }
        // Hash password
        const salt = await bcryptjs_1.default.genSalt(10);
        const hashedPassword = await bcryptjs_1.default.hash(validatedData.personalInfo.password, salt);
        // Create User and LLC Registration in a transaction
        const newLlc = await prisma.$transaction(async (tx) => {
            const user = await tx.user.create({
                data: {
                    email: validatedData.personalInfo.email,
                    password: hashedPassword,
                    firstName: validatedData.personalInfo.firstName,
                    lastName: validatedData.personalInfo.lastName,
                    phone: validatedData.personalInfo.phone,
                    address: validatedData.personalInfo.address,
                    city: validatedData.personalInfo.city,
                    state: validatedData.personalInfo.state,
                    zip: validatedData.personalInfo.zip,
                    partnerCode: validatedData.personalInfo.partnerCode,
                }
            });
            const llc = await tx.llcRegistration.create({
                data: {
                    userId: user.id,
                    companyName: validatedData.companyDetails.companyName,
                    designator: validatedData.companyDetails.designator,
                    state: validatedData.companyDetails.state,
                    industry: validatedData.companyDetails.industry,
                    registeredAgent: validatedData.services.registeredAgent,
                    mailForwarding: validatedData.services.mailForwarding,
                    einApplication: validatedData.services.einApplication,
                    cryptoProtectionActive: validatedData.services.cryptoProtectionActive,
                    walletShippingAddress: validatedData.services.walletShippingAddress,
                    status: 'PENDING_PAYMENT',
                },
            });
            return llc;
        });
        // Send Automated Legal Documentation Email if Crypto Package is selected
        if (newLlc.cryptoProtectionActive) {
            try {
                const transporter = await createTestTransporter();
                const info = await transporter.sendMail({
                    from: '"iCapital Wyoming LLC Legal" <legal@icapitalwyomingllc.com>',
                    to: validatedData.personalInfo.email,
                    subject: "Your Crypto Asset Security Documentation",
                    text: `Dear ${validatedData.personalInfo.firstName},\n\nAttached are your Full Legal Asset Protection Documents for your new LLC: ${newLlc.companyName}.`,
                    html: `<p>Dear ${validatedData.personalInfo.firstName},</p><p>Attached are your <strong>Full Legal Asset Protection Documents</strong> for your new LLC: <strong>${newLlc.companyName}</strong>.</p><p>Your free hardware wallet will be dispatched to the provided shipping address shortly.</p>`
                });
                console.log("Legal Docs Message sent: %s", info.messageId);
                console.log("Preview URL: %s", nodemailer_1.default.getTestMessageUrl(info));
                // Mark legal docs as sent in DB
                await prisma.llcRegistration.update({
                    where: { id: newLlc.id },
                    data: { legalDocsSent: true }
                });
            }
            catch (emailError) {
                console.error("Failed to send automated legal documentation email:", emailError);
                // We don't fail the registration request if the email fails
            }
        }
        // Generate JWT token for auto-login
        const token = jsonwebtoken_1.default.sign({ id: newLlc.userId, email: validatedData.personalInfo.email, role: 'USER' }, JWT_SECRET, { expiresIn: '24h' });
        res.status(201).json({
            success: true,
            orderId: newLlc.id,
            token,
            message: 'LLC Registration submitted successfully. You are now logged in.',
        });
    }
    catch (error) {
        if (error instanceof zod_1.z.ZodError) {
            return res.status(400).json({
                success: false,
                errors: error.issues.map((err) => ({ field: err.path.join('.'), message: err.message })),
            });
        }
        console.error('Error in registerLlc:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.registerLlc = registerLlc;
const contactUs = async (req, res) => {
    try {
        const validatedData = contactSchema.parse(req.body);
        await prisma.contactMessage.create({
            data: {
                name: validatedData.name,
                email: validatedData.email,
                subject: validatedData.subject,
                message: validatedData.message,
            },
        });
        res.status(200).json({
            success: true,
            message: 'Message sent successfully.',
        });
    }
    catch (error) {
        if (error instanceof zod_1.z.ZodError) {
            return res.status(400).json({
                success: false,
                errors: error.issues.map((err) => ({ field: err.path.join('.'), message: err.message })),
            });
        }
        console.error('Error in contactUs:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.contactUs = contactUs;
// Admin Webhook to trigger hardware wallet dispatch
const dispatchWallet = async (req, res) => {
    const { orderId } = req.body;
    // TODO: Add Admin Authentication Guard here
    if (!orderId) {
        return res.status(400).json({ success: false, message: 'Order ID is required' });
    }
    try {
        const llc = await prisma.llcRegistration.findUnique({ where: { id: orderId } });
        if (!llc || !llc.cryptoProtectionActive) {
            return res.status(400).json({ success: false, message: 'Invalid order or crypto package not active' });
        }
        // Mark as dispatched (we reuse the roiTrackingStatus for simplicity, or we could add a dedicated field)
        await prisma.llcRegistration.update({
            where: { id: orderId },
            data: { roiTrackingStatus: 'WALLET_DISPATCHED' }
        });
        return res.status(200).json({ success: true, message: 'Wallet marked for dispatch.' });
    }
    catch (error) {
        console.error('Error in dispatchWallet:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
};
exports.dispatchWallet = dispatchWallet;
