
all: mpconvert

objects:
	$(Q)$(CC) $(CFLAGS) -c -o mpconvert.o mpconvert.c

mpconvert:
	$(Q)$(CC) $(CFLAGS) -o mpconvert mpconvert.c

clean:
	$(Q)rm -f *.o mpconvert

.PHONY: all objects clean
