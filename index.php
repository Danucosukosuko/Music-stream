<?php
session_start();

// Lista de ejemplo para este ejemplo en GitHub
$initialVideos = [
    // Lista de ejemplo para que tu solo tengas que iniciar el servidor PHP
    "QJO3ROT-A4E", "KQ6zr6kCPj8", "9bZkp7q19f0", "fLexgOxsZu0", "RgKAFK5djSk",
    "OPf0YbXqDm0", "YqeW9_5kURI", "0KSOMA3QBU0", "hT_nvWreIhg", "CevxZvSJLk8",
    "SlPhMPnQ58k", "Bznxx12Ptl0", "PIh2xe4jnpk", "7wtfhZwyrcc", "M8uPvX2te0I",
    "HgzGwKwLmgM", "fKopy74weus", "UceaB4D0jpo", "IcrbM1l_BoI", "YQHsXMglC9A",
    "lY2yjAdbvdQ", "JGwWNGJdvx8", "2Vv-BfVoq4g", "2vjPBrBU-TM"
];

// Inicializar la lista de reproducción en la sesión si no existe
if (!isset($_SESSION['playlist'])) {
    $_SESSION['playlist'] = $initialVideos;
}

// Función para obtener un vídeo aleatorio y actualizar la lista de reproducción
function getRandomVideo(&$playlist) {
    if (empty($playlist)) {
        return null; // No hay vídeos disponibles
    }
    $videoId = $playlist[array_rand($playlist)];
    // Asegurarse de que el vídeo se elimine de la lista para evitar duplicados
    $playlist = array_diff($playlist, [$videoId]);
    return $videoId;
}

// Obtener un vídeo aleatorio para reproducir
$videoToPlay = getRandomVideo($_SESSION['playlist']);

// Si la lista de reproducción está vacía, reiniciar
if (empty($_SESSION['playlist'])) {
    $_SESSION['playlist'] = $initialVideos;
    $videoToPlay = getRandomVideo($_SESSION['playlist']);
}

// Función para obtener el título del vídeo
function getVideoTitle($videoId) {
    $html = file_get_contents("https://www.youtube.com/watch?v=$videoId");
    preg_match('/<meta name="title" content="(.+?)">/', $html, $matches);
    return $matches[1] ?? 'Título no disponible';
}

// Obtener el título del vídeo actual
$videoTitle = getVideoTitle($videoToPlay);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reproductor de Música Aleatoria</title>
    <style>
        html, body {
            margin: 0;
            height: 100%;
            overflow: hidden;
        }
        #player {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        #videoTitle {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 16px;
            z-index: 10;
            opacity: 0; /* Inicialmente oculto */
            transition: opacity 0.5s ease-in-out; /* Efecto de transición */
        }
    </style>
</head>
<body>
    <div id="videoTitle">
        Estamos escuchando: <?php echo htmlspecialchars($videoTitle, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div id="player"></div>

    <script>
        // Cargar la API de IFrame de YouTube
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                videoId: '<?php echo $videoToPlay; ?>',
                playerVars: {
                    'autoplay': 1,
                    'controls': 0,
                    'showinfo': 0,
                    'modestbranding': 1,
                    'loop': 0,
                    'mute': 1 // Inicialmente silenciado
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange,
                    'onError': onPlayerError
                }
            });
        }

        function onPlayerReady(event) {
            // Subir el volumen después de un breve retraso para permitir la reproducción automática
            setTimeout(() => {
                player.unMute(); // Activar sonido
                player.setVolume(100); // Establecer volumen al 100%
            }, 2000); // 2 segundos de retraso antes de activar el sonido

            // Mostrar el título del video con fade in después de 7 segundos
            setTimeout(() => {
                var videoTitle = document.getElementById('videoTitle');
                videoTitle.style.opacity = 1; // Mostrar

                // Ocultar el título del video con fade out después de 15 segundos
                setTimeout(() => {
                    videoTitle.style.opacity = 0; // Ocultar
                }, 15000); // 15 segundos
            }, 7000); // 7 segundos de retraso
        }

        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                // Recargar la página para cargar un nuevo video
                location.reload();
            } else if (event.data === YT.PlayerState.UNSTARTED || event.data === YT.PlayerState.BUFFERING) {
                // Si el video no se carga o está en buffer, intentar con otro video
                setTimeout(() => {
                    location.reload();
                }, 5000);
            }
        }

        function onPlayerError(event) {
            console.error("Error en el reproductor de YouTube:", event.data);
            // Intentar con otro video si hay un error
            setTimeout(() => {
                location.reload();
            }, 5000);
        }
    </script>
</body>
</html>
