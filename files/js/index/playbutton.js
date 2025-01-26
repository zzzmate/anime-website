document.addEventListener('DOMContentLoaded', function () {
    const playButton = document.querySelector('.play-button');
    const videoBackground = document.getElementById('recommend-background');

    if (playButton && videoBackground) {
        playButton.addEventListener('click', function (event) {
            event.preventDefault();

            if (videoBackground.classList.contains('hidden')) {
                videoBackground.classList.remove('hidden');
                videoBackground.currentTime = 0;
                videoBackground.play();
                playButton.innerHTML = '<i class="fa-solid fa-stop"></i>';
            } else {
                videoBackground.classList.add('hidden');
                videoBackground.pause();
                playButton.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        });

        videoBackground.addEventListener('play', function () {
            playButton.innerHTML = '<i class="fa-solid fa-stop"></i>';
        });

        videoBackground.addEventListener('pause', function () {
            playButton.innerHTML = '<i class="fa-solid fa-play"></i>';
        });
    }
});