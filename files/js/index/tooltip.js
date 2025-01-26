document.addEventListener('DOMContentLoaded', () => {
    const infoIcon = document.getElementById('info-icon');
    const tooltip = document.getElementById('tooltip');

    infoIcon.addEventListener('mousemove', (event) => {
        tooltip.style.display = 'block';
        tooltip.style.left = `${event.pageX + 10}px`;
        tooltip.style.top = `${event.pageY - 10}px`;
    });

    infoIcon.addEventListener('mouseleave', () => {
        tooltip.style.display = 'none';
    });
});