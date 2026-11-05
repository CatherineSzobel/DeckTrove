import { describe, expect, it } from "vitest";
import fixture from "../../../tests/fixtures/deck-copy-rules.json";
import { formatProblems } from "./rules.js";

// The same cases as tests/Feature/DeckRuleParityTest.php, so the builder refuses what the server refuses, in its words.
describe("copy rules shared with the server", () => {
    it.each(fixture.cases)("$case", ({ type, limits, format, copies, error }) => {
        const card = { id: "1", name: "Test Card", type, limits };
        const problems = formatProblems(fixture.rules, { main: Array(copies).fill(card) }, format);

        expect(problems[0]?.message ?? null).toBe(error);
    });
});
