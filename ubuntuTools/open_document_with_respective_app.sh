#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 <filename>"
    echo "Opens the specified file with the appropriate application. for e.g. an Office Document with LibreOffice and PDF with OS specific pdf reader"
}

# Check if filename is provided as argument
if [ $# -ne 1 ]; then
    usage
    exit 1
fi

filename="$1"

# Get the file extension
extension="${filename##*.}"

# Check the file type and open accordingly
case "$extension" in
    docx | doc)
        # Open with LibreOffice Writer for .docx or .doc files
        soffice --writer "$filename"
        ;;
    pdf)
        # Open with default PDF viewer for .pdf files
        xdg-open "$filename"
        ;;
    *)
        # echo "Unsupported file type"
	xdg-open "$filename"
        usage
        ;;
esac

