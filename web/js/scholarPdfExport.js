function animatePdfExportIcon(event) {
    
    event.preventDefault();
    
    const downloadLink = document.getElementById("pdf-download-link");
    const pdfIcon = downloadLink.querySelector("i");
    const spinner = document.getElementById("loading-spinner");

    // Show spinner, hide PDF icon
    pdfIcon.style.display = "none";
    spinner.style.display = "inline-block";

    // Fetch PDF and trigger download
    fetch(downloadLink.href)
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "bip-scholar-cv.pdf"; // Change filename if needed
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        })
        .catch(() => alert("Failed to generate PDF."))
        .finally(() => {
            // Restore PDF icon and hide spinner
            pdfIcon.style.display = "inline-block";
            spinner.style.display = "none";
        });
}