import { ethers } from 'ethers';

// You would typically define an array of RPC URLs for different networks
const RPC_URLS = {
  ETH: process.env.ETH_RPC_URL || 'https://cloudflare-eth.com',
  POLYGON: process.env.POLYGON_RPC_URL || 'https://polygon-rpc.com',
  BSC: process.env.BSC_RPC_URL || 'https://bsc-dataseed.binance.org/',
};

export const getHotWalletAddress = (): string | null => {
  // If HOT_WALLET_PRIVATE_KEY exists, derive public address
  if (process.env.HOT_WALLET_PRIVATE_KEY) {
    try {
      const wallet = new ethers.Wallet(process.env.HOT_WALLET_PRIVATE_KEY);
      return wallet.address;
    } catch (e) {
      console.error("Invalid hot wallet private key:", e);
      return null;
    }
  }
  // Fallback to explicit public address if private key is not available
  return process.env.HOT_WALLET_PUBLIC_ADDRESS || null;
};

export const getHotWalletBalances = async () => {
  const address = getHotWalletAddress();
  if (!address) {
    return { error: 'No hot wallet configured' };
  }

  const balances: Record<string, string> = {};

  try {
    for (const [network, rpcUrl] of Object.entries(RPC_URLS)) {
      try {
        const provider = new ethers.JsonRpcProvider(rpcUrl);
        const balance = await provider.getBalance(address);
        balances[network] = ethers.formatEther(balance);
      } catch (e) {
        console.error(`Failed to fetch balance for ${network}:`, e);
        balances[network] = '0.0'; // Fallback
      }
    }
    return balances;
  } catch (error) {
    console.error("Error fetching hot wallet balances:", error);
    return { error: 'Failed to fetch balances' };
  }
};
