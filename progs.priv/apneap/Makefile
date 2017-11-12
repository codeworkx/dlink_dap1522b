#
# Makefile for progs.priv/neap
#

-include ../../.config

all:
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPS)),y)
	@if [ ! -d apneaps ]; then \
		mkdir apneaps; \
		cp Makefile.apneaps ./apneaps/Makefile; \
	fi
	@echo -e "\033[32mBuilding apneaps ...\033[0m"
	make -C apneaps;
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPC)),y)
	@if [ ! -d apneapc ]; then \
		mkdir apneapc; \
		cp Makefile.apneapc ./apneapc/Makefile; \
	fi
	@echo -e "\033[32mBuilding apneapc ...\033[0m"
	make -C apneapc;
		
	
	@if [ ! -d aparraymsg ]; then \
		mkdir aparraymsg; \
		cp Makefile.aparraymsg ./aparraymsg/Makefile; \
	fi
	make -C aparraymsg;
	#2009_11_9 sandy
	@if [ ! -d apneaps_array ]; then \
		mkdir apneaps_array; \
		cp Makefile.apneaps_array ./apneaps_array/Makefile; \
	fi
	@echo -e "\033[32mBuilding apneaps_array ...\033[0m"
	make -C apneaps_array;
endif

install:
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPS)),y)
	@echo -e "\033[32mInstalling apneaps ...\033[0m"
	make -C apneaps install;
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPC)),y)
	@echo -e "\033[32mInstalling apneapc ...\033[0m"
	make -C apneapc install;
	make -C aparraymsg install;
	make -C apneaps_array install;
endif

clean:
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPS)),y)
	@echo -e "\033[32mCleaning apneaps ...\033[0m"
	if [ -d "apneaps" ]; then make -C apneaps clean; fi;
	rm -rf apneaps;
endif
ifeq ($(strip $(ELBOX_PROGS_PRIV_AP_NEAPC)),y)
	@echo -e "\033[32mCleaning apneapc ...\033[0m"
	if [ -d "apneapc" ]; then make -C apneapc clean; fi;
	rm -rf apneapc;
	
	@echo -e "\033[32mCleaning aparraymsg ...\033[0m"
	if [ -d "aparraymsg" ]; then make -C aparraymsg clean; fi;
	rm -rf aparraymsg;
	
	@echo -e "\033[32mCleaning apneaps_array ...\033[0m"
	if [ -d "apneaps_array" ]; then make -C apneaps_array clean; fi;
	rm -rf apneaps_array;
endif

.PHONY: all install clean
