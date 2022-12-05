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
@app.route("/c2", methods=['GET', 'POST'])
def send_form():
    default= 'none'
    id = request.args.get('id', default) 
    ctps = request.args.get('ctps', default) 
    email_empresa = request.args.get('email_empresa', default) 
    def align(img):
        data=detector.detect_faces(img)
        biggest=0
        if data !=[]:
            for faces in data:
                box=faces['box']            
                # calculate the area in the image
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
        #y=box[1] h=box[3] x=box[0] w=box[2]   
        biggest=0
        if data !=[]:
            for faces in data:
                box=faces['box']            
                # calculate the area in the image
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
        #rotates an image by the degree angle
        # grab the dimensions of the image and then determine the center
        (h, w) = image.shape[:2]
        (cX, cY) = (w // 2, h // 2)
        # grab the rotation matrix (applying the angle to rotate clockwise), then grab the sine and cosine
        # (i.e., the rotation components of the matrix)
        M = cv2.getRotationMatrix2D((cX, cY), angle, 1.0)
        cos = np.abs(M[0, 0])
        sin = np.abs(M[0, 1]) 
        # compute the new bounding dimensions of the image
        nW = int((h * sin) + (w * cos))
        nH = int((h * cos) + (w * sin)) 
        # adjust the rotation matrix to take into account translation
        M[0, 2] += (nW / 2) - cX
        M[1, 2] += (nH / 2) - cY 
        # perform the actual rotation and return the image
        return cv2.warpAffine(image, M, (nW, nH)) 
    def align_crop_resize(sdir,dest_dir, height=None, width= None): 
        cropped_dir=os.path.join(dest_dir, 'Imagens')
        if os.path.isdir(dest_dir):
            shutil.rmtree(dest_dir)
        os.mkdir(dest_dir)  #start with an empty destination directory
        os.mkdir(cropped_dir)
        flist=os.listdir(sdir) #get a list of the image files    
        success_count=0
        for i,f in enumerate(flist): # iterate through the image files
            fpath=os.path.join(sdir,f)        
            if os.path.isfile(fpath) and i <10:
                try:
                    img=cv2.imread(fpath) # read in the image
                    shape=img.shape
                    status,img=align(img) # rotates the image for the eyes are horizontal
                    if status:             
                        cstatus, img=crop_image(img) # crops the aligned image to return the largest face
                        if cstatus:
                            if height != None and width !=None:
                                img=cv2.resize(img, (height, width)) # if height annd width are specified resize the image
                            cropped_path=os.path.join(cropped_dir, f)
                            cv2.imwrite(cropped_path, img) # save the image
                            success_count +=1 # update the coount of successful processed images
                    
                except:
                    print('file ', fpath, ' is a bad image file')
        return success_count
    
    detector = MTCNN()
    sdir=(r"/home/site/wwwroot/"+email_empresa+"/"+ctps)
    working_dir=r'./'
    dest_dir=os.path.join(working_dir, id)
    height=128
    width=128
    count=align_crop_resize(sdir,dest_dir)
    print ('Number of sucessfully processed images= ', count)

    def show_images(tdir):
        filelist=os.listdir(tdir)
        length=len(filelist)
        columns=5
        rows=int(np.ceil(length/columns))    
        plt.figure(figsize=(20, rows * 4))
        for i, f in enumerate(filelist):    
            fpath=os.path.join(tdir, f)
            imgpath=os.path.join(tdir,f)
            img=plt.imread(imgpath)
            plt.subplot(rows, columns, i+1)
            plt.axis('off')
            plt.title(f, color='blue', fontsize=12)
            plt.imshow(img)

    show_dir=os.path.join(dest_dir, email_empresa)
    show_images(show_dir)
if __name__ == "__main__":
      app.debug = True
      app.run(port=5000)
