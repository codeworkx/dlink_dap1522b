# wpatalk -- talk to running wpa_supplicant or hostapd program
#
#
include ../../arch.mk
include ../../../path.mk

include ../common/full.config
include ../common/Makefile.common

#LIBS += -L../common/ -lwpa_ctrl
LIBS_FOR_WPATALK += -L../common/ -lwpa_ctrl

ALL = wpatalk

all: $(ALL)
	echo All done for wpatalk

install: all
	cp -a $(ALL) $(TARGET)/usr/sbin/.	
	#cp -a $(ALL) $(INSTALL_ROOT)/sbin/.

# Uncomment to use readline (but makes program GPL only)
#CFLAGS += -DCONFIG_READLINE

OBJS = wpatalk.o

wpatalk: $(OBJS) $(COMMON_LIBS)
	$(CC) -o wpatalk $(OBJS) $(LIBS_FOR_WPATALK)
	#$(CC) -o wpatalk $(OBJS) $(LIBS)
	

clean:
	rm -f core junk* *~ *.o *.d *.a *.so $(ALL) $(WINALL)

gpl:
	rm -f *.h *.c *.o
	mv Makefile.gpl Makefile
-include $(OBJS:%.o=%.d)

