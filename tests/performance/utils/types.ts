export enum Scenario {
	Default = 'Default',
	GingerMo = 'Ginger MO (MO)',
	GingerMoPhp = 'Ginger MO (PHP)',
}

export type TestCase = {
	locale: string;
	scenario: Scenario;
};
