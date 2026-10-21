// Run in a loaded documentation page with: agent-browser eval --stdin < tools/docs/check-layout.js
// Exercise 390px, 1280px and 1920px viewports, including a guide and an API page.
(() => {
    const main = document.querySelector('main.phpdocumentor');
    const footer = document.querySelector('footer.a8c-footer');
    const header = document.querySelector('header.phpdocumentor-header');
    const body = main?.querySelector(':scope > .phpdocumentor-section');
    if (!main || !footer || !header || !body) throw new Error('Documentation header/body/footer missing.');
    const regions = [main, ...main.querySelectorAll('.phpdocumentor-content, .phpdocumentor-sidebar')]
        .map(element => element.getBoundingClientRect())
        .filter(rect => rect.height > 0);
    const bottom = Math.max(...regions.map(rect => rect.bottom));
    const box = footer.getBoundingClientRect();
    if (box.top + 1 < bottom) throw new Error(`Footer beside content: top ${box.top}, content bottom ${bottom}.`);
    const content = body.getBoundingClientRect();
    // Layout is centered in the content viewport, excluding a non-overlay scrollbar.
    const viewport = document.documentElement.clientWidth;
    for (const [name, element] of [['Header', header], ['Footer', footer]]) {
        const region = element.getBoundingClientRect();
        if (Math.abs(region.left - content.left) > 1 || Math.abs(region.width - content.width) > 1) {
            throw new Error(`${name} does not match body container: left ${region.left}, width ${region.width}; body ${content.left}/${content.width}.`);
        }
        if (Math.abs(region.left - (viewport - region.right)) > 1) {
            throw new Error(`${name} is not centered: left ${region.left}, right ${region.right}, available width ${viewport}.`);
        }
    }
    if (document.documentElement.scrollWidth > viewport + 1) throw new Error('Horizontal page overflow.');
    return {url: location.href, viewport: innerWidth, availableWidth: viewport, contentBottom: bottom, footerTop: box.top,
        containerLeft: content.left, containerWidth: content.width, headerWidth: header.getBoundingClientRect().width, footerWidth: box.width};
})();
