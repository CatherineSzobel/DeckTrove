import { describe, expect, test, vi } from "vitest";
import { profileForm } from "./profile-form.js";

const fakeUrls = () => ({ createObjectURL: vi.fn((file) => `blob:${file.name}`), revokeObjectURL: vi.fn() });
const fileEvent = (...files) => ({ target: { files } });

describe("profile form", () => {
    test("counts the about text against the limit", () => {
        const form = profileForm(14);

        expect(form.counter).toBe("14 / 500");
        form.count({ target: { value: "Hi" } });
        expect(form.counter).toBe("2 / 500");
    });

    test("previews a picked picture and frees the previous one", () => {
        const urls = fakeUrls();
        const form = profileForm(0, 500, urls);

        form.pick(fileEvent({ name: "a.png" }));
        expect(form.preview).toBe("blob:a.png");

        form.pick(fileEvent({ name: "b.png" }));
        expect(urls.revokeObjectURL).toHaveBeenCalledWith("blob:a.png");
        expect(form.preview).toBe("blob:b.png");
    });

    test("clears the preview when no file is picked", () => {
        const urls = fakeUrls();
        const form = profileForm(0, 500, urls);

        form.pick(fileEvent({ name: "a.png" }));
        form.pick(fileEvent());
        expect(form.preview).toBeNull();
        expect(urls.revokeObjectURL).toHaveBeenCalledWith("blob:a.png");
    });
});
