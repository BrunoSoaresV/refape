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
import sys
from subprocess import call
id = sys.argv[1] 
ctps =sys.argv[2] 
email_empresa = sys.argv[3] 
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
        img=img[bbox[1]-500 : bbox[1]+bbox[3]+500, bbox[0] -500: bbox[0]+bbox[2]+500]     
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
sdir=("pasta/"+email_empresa+"/"+ctps)
working_dir=r'./'
dest_dir=os.path.join(working_dir, id)
height=128
width=128
count=align_crop_resize(sdir,dest_dir)
print ('Total de imagens processadas com sucesso: ', count)
