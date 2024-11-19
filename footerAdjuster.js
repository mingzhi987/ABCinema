function adjustFooter() {
    const footer = document.getElementById('footer');
    const contentHeight = document.body.scrollHeight;
    const screenHeight = screen.height - 200;

    console.log(contentHeight);
    console.log("screen height: " + screenHeight);

    if (contentHeight < screenHeight) {
        console.log("yes");
        footer.style.position = 'absolute';
        footer.style.bottom = '0';
        footer.style.width = '100%';
    } else {
        console.log("no");
        footer.style.position = 'relative';
    }
}

window.onload = adjustFooter;
window.addEventListener('resize', adjustFooter);