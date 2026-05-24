import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

const assets = [
  { symbol: 'USDT', name: 'Tether USD', network: 'Ethereum', iconColor: '#26A17B', iconUrl: 'https://assets.coingecko.com/coins/images/325/large/Tether-logo.png', price: 0.9995, change: 0.00 },
  { symbol: 'BTC', name: 'Bitcoin', network: 'Bitcoin', iconColor: '#F7931A', iconUrl: 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png', price: 77947.00, change: -0.34 },
  { symbol: 'ETH', name: 'Ethereum', network: 'Ethereum', iconColor: '#627EEA', iconUrl: 'https://assets.coingecko.com/coins/images/279/large/ethereum.png', price: 2180.50, change: 0.24 },
  { symbol: 'BNB', name: 'BNB', network: 'BSC', iconColor: '#F3BA2F', iconUrl: 'https://assets.coingecko.com/coins/images/825/large/binance-coin-logo.png', price: 651.20, change: -0.81 },
  { symbol: 'SOL', name: 'Solana', network: 'Solana', iconColor: '#14F195', iconUrl: 'https://assets.coingecko.com/coins/images/4128/large/coinmarketcap-solana-200.png', price: 86.22, change: -0.44 },
  { symbol: 'TRX', name: 'TRON', network: 'Tron', iconColor: '#FF0013', iconUrl: 'https://coin-images.coingecko.com/coins/images/1094/large/tron-logo.png', price: 0.3567, change: 0.67 },
  { symbol: 'DOGE', name: 'Dogecoin', network: 'Dogecoin', iconColor: '#C2A633', iconUrl: 'https://assets.coingecko.com/coins/images/5/large/dogecoin.png', price: 0.1100, change: 0.22 },
  { symbol: 'SHIB', name: 'Shiba Inu', network: 'Ethereum', iconColor: '#FFA409', iconUrl: 'https://assets.coingecko.com/coins/images/11939/large/shiba.png', price: 0.00000579, change: -1.46 },
  { symbol: 'XRP', name: 'Ripple', network: 'Ripple', iconColor: '#23292F', iconUrl: 'https://assets.coingecko.com/coins/images/44/large/xrp.png', price: 1.41, change: -0.42 },
];

async function main() {
  console.log('Seeding assets...');
  for (const asset of assets) {
    await prisma.walletAsset.upsert({
      where: { symbol: asset.symbol },
      update: asset,
      create: asset,
    });
  }
  console.log('Assets seeded successfully!');
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
