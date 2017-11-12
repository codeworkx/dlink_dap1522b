
all: dayconvert
objects: dayconvert.o

dayconvert.o:
	$(Q)$(CC) $(CFLAGS) -c -o dayconvert.o dayconvert.c

dayconvert:
	$(Q)$(CC) $(CFLAGS) -o dayconvert dayconvert.c

clean:
	$(Q)rm -f *.o dayconvert

.PHONY: all objects clean
