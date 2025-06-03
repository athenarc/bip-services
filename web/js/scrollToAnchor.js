document.addEventListener("DOMContentLoaded", function () {
    const OFFSET = 70; // Adjust this value to your needs

    if (window.location.hash) {
        const targetId = window.location.hash.substring(1);
        const targetElement = document.getElementById(targetId);
        if (targetElement) {
            setTimeout(() => {
                const top = targetElement.getBoundingClientRect().top + window.pageYOffset - OFFSET;
                window.scrollTo({ top, behavior: "smooth" });
            }, 100); // slight delay to wait for rendering
        }
    }

    // Also handle internal page anchor clicks
    document.querySelectorAll("a.scroll-offset").forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            const href = this.getAttribute("href");
            if (href.includes("#")) {
                const id = href.split("#")[1];
                const target = document.getElementById(id);
                if (target) {
                    e.preventDefault();
                    const top = target.getBoundingClientRect().top + window.pageYOffset - OFFSET;
                    window.scrollTo({ top, behavior: "smooth" });
                    // update URL without reloading
                    history.pushState(null, null, href);
                }
            }
        });
    });
});