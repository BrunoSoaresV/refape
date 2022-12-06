import tensorflow
import subprocess as sp
from mtcnn import MTCNN
import cv2
import os
import numpy as np
from tqdm import tqdm
import shutil
import matplotlib.pyplot as plt
from matplotlib.pyplot import imshow
from flask import *
from subprocess import call
app = Flask(__name__)
@app.route('/')
def index():
  return render_template('index.html')
@app.route("/c2.php", methods=['GET', 'POST'])
def pegardados():
    default= 'none'
    id = request.form.get('id', default) 
    ctps = request.form.get('ctps', default) 
    email_empresa = request.form.get('email_empresa', default)
    out = sp.run(["php", "c2.php"], stdout=sp.PIPE) 
    def align(img):
        data=detector.detect_faces(img)
        biggest=0
        if data !=[]:
            for faces in data:
                box=faces['box']            
                area = box[3]  * box[2]
                if area>biggest:
                    biggest=area
                    bbox=box                
                    keypoints=faces['keypoints']
                    left_eye=keypoints['left_eye']
                    right_eye=keypoints['right_eye']                 
            lx,ly=left_eye        
            rx,ry=right_eye
            dx=rx-lx
            dy=ry-ly
            tan=dy/dx
            theta=np.arctan(tan)
            theta=np.degrees(theta)    
            img=rotate_bound(img, theta)        
            return (True,img)
        else:
            return (False, None)
    def crop_image(img): 
        data=detector.detect_faces(img)   
        biggest=0
        if data !=[]:
            for faces in data:
                box=faces['box']            
                area = box[3]  * box[2]
                if area>biggest:
                    biggest=area
                    bbox=box 
            bbox[0]= 0 if bbox[0]<0 else bbox[0]
            bbox[1]= 0 if bbox[1]<0 else bbox[1]
            img=img[bbox[1]: bbox[1]+bbox[3],bbox[0]: bbox[0]+ bbox[2]]        
            return (True, img) 
        else:
            return (False, None)
        
    def rotate_bound(image, angle):
        (h, w) = image.shape[:2]
        (cX, cY) = (w // 2, h // 2)
        M = cv2.getRotationMatrix2D((cX, cY), angle, 1.0)
        cos = np.abs(M[0, 0])
        sin = np.abs(M[0, 1]) 
        nW = int((h * sin) + (w * cos))
        nH = int((h * cos) + (w * sin)) 
        M[0, 2] += (nW / 2) - cX
        M[1, 2] += (nH / 2) - cY 
        return cv2.warpAffine(image, M, (nW, nH)) 
    def align_crop_resize(sdir,dest_dir, height=None, width= None): 
        cropped_dir=os.path.join(dest_dir, email_empresa)
        if os.path.isdir(dest_dir):
            shutil.rmtree(dest_dir)
        os.mkdir(dest_dir)  
        os.mkdir(cropped_dir)
        flist=os.listdir(sdir)     
        success_count=0
        for i,f in enumerate(flist): 
            fpath=os.path.join(sdir,f)        
            if os.path.isfile(fpath) and i <10:
                try:
                    img=cv2.imread(fpath) 
                    shape=img.shape
                    status,img=align(img)
                    if status:             
                        cstatus, img=crop_image(img) 
                        if cstatus:
                            if height != None and width !=None:
                                img=cv2.resize(img, (height, width)) 
                            cropped_path=os.path.join(cropped_dir, f)
                            cv2.imwrite(cropped_path, img) 
                            success_count +=1 
                    
                except:
                    print('A imagem:', fpath, ' não é uma boa imagem')
        return success_count
    detector = MTCNN()
    sdir=("pasta\\"+email_empresa+"\\"+ctps)
    working_dir=r'./'
    dest_dir=os.path.join(working_dir, id)
    height=128
    width=128
    count=align_crop_resize(sdir,dest_dir)
    print ('Total de imagens processadas com sucesso: ', count)
    return out.stdout
@app.route('/cadastrofuncionarios.php')
def cadastrofuncionarios():
     out = sp.run(["php", "cadastrofuncionarios.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/login.php')
def login():
     out = sp.run(["php", "login.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/l1.php', methods=['GET', 'POST'])
def l1():
     out = sp.run(["php", "l1.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/c1.php', methods=['GET', 'POST'])
def c1():
     out = sp.run(["php", "c1.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/cadastro.php')
def cadastro():
     out = sp.run(["php", "cadastro.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/home.php')
def home():
     out = sp.run(["php", "home.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/listagem.php', methods=['GET', 'POST'])
def listagem():
     out = sp.run(["php", "listagem.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/conexao.php')
def conexao():
     out = sp.run(["php", "conexao.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/desativado.php')
def desativado():
     out = sp.run(["php", "desativado.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/e1.php', methods=['GET', 'POST'])
def e1():
     out = sp.run(["php", "e1.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/dfuncionario.php', methods=['GET', 'POST'])
def dfuncionario():
     out = sp.run(["php", "dfuncionario.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/editar.php')
def editar():
     out = sp.run(["php", "editar.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/ffuncionario.php', methods=['GET', 'POST'])
def ffuncionario():
     out = sp.run(["php", "ffuncionario.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/ldponto.php', methods=['GET', 'POST'])
def ldponto():
     out = sp.run(["php", "ldponto.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/lfponto.php', methods=['GET', 'POST'])
def lfponto():
     out = sp.run(["php", "lfponto.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/ponto.php')
def ponto():
     out = sp.run(["php", "ponto.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/protecao.php')
def protecao():
     out = sp.run(["php", "protecao.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/reconhecimentofacial.php', methods=['GET', 'POST'])
def reconhecimentofacial():
     out = sp.run(["php", "reconhecimentofacial.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/sair.php')
def sair():
     out = sp.run(["php", "sair.php"], stdout=sp.PIPE)
     return out.stdout
@app.route('/verificarpontos.php')
def verificarpontos():
     out = sp.run(["php", "verificarpontos.php"], stdout=sp.PIPE)
     return out.stdout
if __name__ == "__main__":
     app.run(host='0.0.0.0',port="8000",debug=True)
