$(document).ready(function () {
    $('.custom-collapse').on("click", function () {
        const $this = $(this);
        const isExpanded = $this.attr("aria-expanded") === "true";
        $this.attr("aria-expanded", !isExpanded);
        $this.find('#custom_expand_icon').toggleClass("fa-chevron-down fa-chevron-up");
    });
});
