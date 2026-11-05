import { describe, expect, it, vi } from "vitest";
import { previewTrigger } from "./preview.js";

describe("previewTrigger", () => {
    it("shows the scope's card on hover and focus, and hides it on leave and blur", () => {
        const scope = { card: { id: "1" }, $el: {}, showPreview: vi.fn(), hidePreview: vi.fn() };
        const bindings = previewTrigger();

        bindings["@mouseenter"].call(scope);
        bindings["@focus"].call(scope);
        bindings["@mouseleave"].call(scope);
        bindings["@blur"].call(scope);

        expect(scope.showPreview).toHaveBeenCalledTimes(2);
        expect(scope.showPreview).toHaveBeenCalledWith(scope.card, scope.$el);
        expect(scope.hidePreview).toHaveBeenCalledTimes(2);
    });
});
