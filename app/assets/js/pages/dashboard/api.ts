export interface DashboardCounts {
    greylist: number,
    autoWhitelistEmails: number,
    autoWhitelistDomains: number,
    whitelistEmails: number,
    whitelistDomains: number,
    blacklistEmails: number,
    blacklistDomains: number,
    users: number,
    aliases: number,
}

export interface ActivityBucket {
    date: string,
    greylisted: number,
    autoWhitelisted: number,
}

export interface Activity {
    days: number,
    buckets: ActivityBucket[],
}

export const ACTIVITY_RANGES = [7, 14, 30] as const;
export type ActivityRange = typeof ACTIVITY_RANGES[number];
