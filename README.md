This project is intended to simplify and automate the process of recovering deleted files or formatted drives using the command ICAT.

To use it, generate an image file using the dd command

Then use the fls command to generate a list of the locations of each deleted file within the image file.

fls ipod.img-r-d-e-f fat> filelist.txt

After generating the file with the command filelist fls, recovery.php run the script from the directory where you want the files to be extracted from the image being placed.

recovery.php php / path / to / filelist.txt / path / to / filesystem.img