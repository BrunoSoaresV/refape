<?php
include("protecao.php");
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script language="JavaScript" src="static/jquery-3.6.0.js"></script>
<script language="JavaScript" src="static/face-api.min.js"></script>
<link href="static/imagem1.png" rel="icon">
<title>Registrar ponto</title>
<style>
            @keyframes progress-content {
                0% {
                    content: '0 de 100%';
                }

                10% {
                    content: '10 de 100%';
                }

                20% {
                    content: '20 de 100%';
                }

                30% {
                    content: '30 de 100%';
                }

                40% {
                    content: '40 de 100%';
                }

                50% {
                    content: '50 de 100%';
                }

                60% {
                    content: '60 de 100%';
                }

                70% {
                    content: '70 de 100%';
                }

                80% {
                    content: '80 de 100%';
                }

                90% {
                    content: '90 de 100%';
                }

                100% {
                    content: '100 de 100%';
                }
            }

            @keyframes progress-bar {
                0% {
                    width: 0%;
                }

                100% {
                    width: 100%
                }
            }

            body {
                margin: 0;
                padding: 0;
            }
            .progress {
                color: black;
                width: 100%;
                height: 60px;
                position: relative;
                background-color: rgb(94, 87, 199);
                animation: progress-bar 5s linear
            }

            .progress::after {
                content: '100 de 100%';
                position: absolute;
                width: 100vw;
                height: 60px;
                text-align: left;
                color: black;
                font-weight: bold;
                line-height: 60px;
                animation: progress-content 5s linear;
            }
            body {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        canvas {
            position: absolute;
        }
        #dados,#id{
            display: flex;
            justify-content: center;
            align-items: center;
            bottom: 10%;
            position: absolute;
            width: 555px;
             margin: auto;
        }
        #segundo, a{
            display: flex;
            justify-content: center;
            align-items: center;
            bottom: 20%;
            position: absolute;
        }
        #camera{      
        width: 100%;
        }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {
                setInterval(function () {
                    $('.desaparecer').fadeOut(1)
                    $('.camera').fadeIn(1)
                }, 5000)
            })
        </script>
    </head>

    <body>
        <div class="camera" style="display: none;">
        <video id="camera" autoplay muted></video>
    <canvas id="canvas"></canvas>
    <div id="id" class="alert alert-light" role="alert">
    </div>
    <div id="dados" class="alert alert-warning" role="alert">
    Aguarde... Isso pode levar alguns minutos.
    </div>
    </div>
        <div class="desaparecer">Aguarde enquanto preparamos seus dados...
            <div class="progress">
            </div>
        </div>
<script>
    setTimeout(function(){ 
        $('#dados').remove();
        }, 20000);
    const camera = document.getElementById("camera")
    Promise.all([
         faceapi.nets.tinyFaceDetector.loadFromUri('./models'),
         faceapi.nets.faceRecognitionNet.loadFromUri('./models'),
         faceapi.nets.faceLandmark68Net.loadFromUri('./models'),
         faceapi.nets.ssdMobilenetv1.loadFromUri('./models')
    ]).then(startVideo())
    async function startVideo() {
        navigator.getUserMedia({
                video: {}
            },
            stream => camera.srcObject = stream,
            erro => console.error(erro)
        )
    }
    const loadLabels = () => {
        <?php 
        require_once("conexao.php");
        $email_empresa=$_SESSION['email'];
        $sql3 = "SELECT * FROM refape_web.funcionario WHERE  email_empresa='$email_empresa' AND status='t' ;";
        $resultado3=pg_query($conexao,$sql3);
        while($linha3 = pg_fetch_assoc($resultado3)){
        $id1=strval($linha3['id']);
        $id[]= str_pad($id1, 16, 0, STR_PAD_LEFT);
  } ?>
  const labels=[<?php  foreach($id as $teste){
                    echo     "\"$teste\"" . ' ,'; 
        } ?>]
            return Promise.all(labels.map(async label => {
            const descriptions = []
            for (let i = 1; i <= 2; i++) {
                const img = await faceapi.fetchImage(`./${label}/<?php echo $email_empresa; ?>/${i}.png`)
                const detections = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor()
                descriptions.push(detections.descriptor)
            }
            return new faceapi.LabeledFaceDescriptors(label, descriptions)
        }))
    }
     
    camera.addEventListener('play', async () => {
    const canvas = faceapi.createCanvasFromMedia(camera)
    document.body.append(canvas)
    camera.width = 450
    camera.height = 450
    const tamanho = {
        width: camera.width,
        height: camera.height
    }
        const labels = await loadLabels()
        faceapi.matchDimensions(canvas, tamanho)
        setInterval(async () => {
            const d = await faceapi.detectAllFaces(camera, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors()
            const a = faceapi.resizeResults(d, tamanho)
            const faceMatcher = new faceapi.FaceMatcher(labels, 0.43)
            const results = a.map(d => faceMatcher.findBestMatch(d.descriptor))
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height)
            faceapi.draw.drawDetections(canvas, a)
            results.forEach((result, index) => {
            const box = a[index].detection.box
            const {label} = result
            const drawBox = new faceapi.draw.DrawBox(box, {label})
            drawBox.draw(canvas)
            $.ajax({
                 url: 'ponto.php',
                 type: 'GET',
                 data: {data: label}
                 }).done(function(resultado) {
                 var inserir = document.getElementById("id")
                  if(resultado==""){
                    setTimeout(function() {
                    inserir.innerHTML = resultado
                    },999999)
                  }else{
                    $(document).ready(function() {
                    switch($('#id').attr('class')){
                        case "alert alert-light": 
                            $("#id").toggleClass("alert-light alert-success")
                            break
                    }
                });
                         inserir.innerHTML = resultado
                  }
                })
            })
        }, 100)
    })
</script>
</body>
</html>