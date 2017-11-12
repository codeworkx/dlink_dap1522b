=========================
0. Intriduction

   This file will show you how to build the GPL linux system.
   
Step 1.	Install fedora linux 10, and make sure you can connect to internet

	Run command as below:
		#yum -y update
		#yum -y install gcc
		#yum -y install zlib-devel openssl-devel
		#yum -y install gcc-c++
		#yum -y install bison
		#yum -y install flex
		#yum -y install ncurses-devel
		#yum -y install fakeroot
	
Step 2.	Setup Build Enviornment($means command)

	1) please login as a normal user such as john,and copy the gpl file to normal user folder,
	
	such as the folder /home/john
	
	2) $cd /home/john
	
	3) $tar zxvf DAP1522B1_GPL207.tar.gz
	
	4) $cd DAP1522B1_GPL207
	
	5) #cp -rf mipsel_gcc-4.3.3_uclibc-0.9.30.1 /opt	(ps : switch to root permission)
		  
	6) $source ./setupenv	(ps : switch back to normal user permission)
	
Step 3. Building the image
	1) $make
	2) $make
	3) $make
     	===================================================
	 You are going to build the f/w images.
	 Both the release and tftp images will be generated.
	 ===================================================
	 Do you want to rebuild the linux kernel ? (yes/no) : yes
	 
      4) there are some options need to be selected , please input "enter" key to execute the default action. 
	 
      5) After make successfully, you will find the image file in ./images/.
 

