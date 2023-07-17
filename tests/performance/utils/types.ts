export enum Scenario {
	Default = 'Default',
	GingerMo = 'Ginger MO (MO)',
	GingerMoPhp = 'Ginger MO (PHP)',
	GingerMoJson = 'Ginger MO (JSON)',
}

export type TestCase = {
	locale: string;
	scenario: Scenario;
	localeSwitching: boolean;
};
