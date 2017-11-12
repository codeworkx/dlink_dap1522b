
all: apmoduletool

apmoduletool: lrgbin.o apmoduletool.o
	$(Q)$(CC) -o apmoduletool libxmldbc.o apmoduletool.o

libxmldbc.o:libxmldbc.c
	$(Q)$(CC) -c -I../../../include ../../../comlib/libxmldbc.c
	
apmoduletool.o:apmoduletool.c
	$(Q)$(CC) -c $(CFLAGS) -I.. apmoduletool.c

clean:
	$(Q)rm -f *.o *.gdb *.elf apmoduletool

.PHONY: all objects clean
