import format from 'date-fns/format';

export const NAMESPACE = 'Sitegeist.Bitzer:Plugin';
export const TODAY = format(new Date(), 'yyyy-MM-dd');
export const PATTERN_INTERVAL = /^(P|PT)(\d+)([A-Z])$/;