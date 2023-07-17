import lighthouse from 'lighthouse';
import type { Page } from '@playwright/test';

class Metrics {
	constructor( public readonly page: Page, public readonly port: number ) {
		this.page = page;
		this.port = port;
	}

	/**
	 * Returns durations from the Server-Timing header.
	 *
	 * @param fields Optional fields to filter.
	 */
	async getServerTiming( fields: string[] = [] ) {
		return this.page.evaluate< Record< string, number >, string[] >(
			( f: string[] ) =>
				(
					performance.getEntriesByType(
						'navigation'
					) as PerformanceNavigationTiming[]
				 )[ 0 ].serverTiming.reduce( ( acc, entry ) => {
					if ( f.length === 0 || f.includes( entry.name ) ) {
						acc[ entry.name ] = entry.duration;
					}
					return acc;
				}, {} as Record< string, number > ),
			fields
		);
	}

	/**
	 * Returns time to first byte (TTFB) from PerformanceObserver.
	 */
	async getTimeToFirstByte() {
		return this.page.evaluate< number >(
			() =>
				(
					performance.getEntriesByType(
						'navigation'
					) as PerformanceNavigationTiming[]
				 )[ 0 ].responseStart
		);
	}

	async getLighthouseReport() {
		const result = await lighthouse(
			this.page.url(),
			{ port: this.port },
			undefined
		);

		if ( ! result ) {
			return {} as Record< string, number >;
		}

		const { lhr } = result;

		const LCP = lhr.audits[ 'largest-contentful-paint' ].numericValue || 0;
		const TBT = lhr.audits[ 'total-blocking-time' ].numericValue || 0;
		const TTI = lhr.audits.interactive.numericValue || 0;
		const CLS = lhr.audits[ 'cumulative-layout-shift' ].numericValue || 0;

		return {
			LCP,
			TBT,
			TTI,
			CLS,
		};
	}
}

export default Metrics;
