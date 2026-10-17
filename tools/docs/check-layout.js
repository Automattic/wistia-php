// Run in a loaded documentation page with: agent-browser eval --stdin < tools/docs/check-layout.js
// Exercise 390px, 1280px and 1920px viewports, including a guide and an API page.
(() => {
    const main = document.querySelector('main.phpdocumentor');
    const footer = document.querySelector('footer.a8c-footer');
    if (!main || !footer) throw new Error('Documentation main/footer missing.');
    const regions = [main, ...main.querySelectorAll('.phpdocumentor-content, .phpdocumentor-sidebar')]
        .map(element => element.getBoundingClientRect())
        .filter(rect => rect.height > 0);
    const bottom = Math.max(...regions.map(rect => rect.bottom));
    const box = footer.getBoundingClientRect();
    if (box.top + 1 < bottom) throw new Error(`Footer beside content: top ${box.top}, content bottom ${bottom}.`);
    if (Math.abs(box.left) > 1 || Math.abs(box.width - innerWidth) > 1) {
        throw new Error(`Footer is not full width: left ${box.left}, width ${box.width}, viewport ${innerWidth}.`);
    }
    if (document.documentElement.scrollWidth > innerWidth + 1) throw new Error('Horizontal page overflow.');
    return {url: location.href, viewport: innerWidth, contentBottom: bottom, footerTop: box.top, footerWidth: box.width};
})();
