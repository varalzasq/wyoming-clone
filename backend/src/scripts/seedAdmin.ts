import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function seedAdmin() {
  const ADMIN_EMAIL = 'admin@icapitalweb3llc.com';
  const ADMIN_PASSWORD = 'Treasury#2025!Secure';

  // Check if admin already exists
  const existing = await prisma.user.findUnique({
    where: { email: ADMIN_EMAIL }
  });

  if (existing) {
    // Ensure role is ADMIN
    if (existing.role !== 'ADMIN') {
      await prisma.user.update({
        where: { email: ADMIN_EMAIL },
        data: { role: 'ADMIN' }
      });
      console.log('✅ Existing user promoted to ADMIN.');
    } else {
      console.log('✅ Admin account already exists.');
    }
    return;
  }

  const salt = await bcrypt.genSalt(10);
  const hashedPassword = await bcrypt.hash(ADMIN_PASSWORD, salt);

  await prisma.user.create({
    data: {
      email: ADMIN_EMAIL,
      password: hashedPassword,
      firstName: 'Platform',
      lastName: 'Admin',
      phone: '0000000000',
      role: 'ADMIN',
    }
  });

  console.log('✅ Admin account created successfully!');
  console.log(`   Email:    ${ADMIN_EMAIL}`);
  console.log(`   Password: ${ADMIN_PASSWORD}`);
}

seedAdmin()
  .catch((e) => {
    console.error('❌ Failed to seed admin:', e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());
