# barcode-generator

This is a testing repo for barcode generated in PDF.

    # Build the image
    docker build -t barcode-generator .

    # Run the container
    docker run -p 8080:80 barcode-generator

    # Run with volume mount for PDF output
    docker run -p 8080:80 -v ${PWD}/pdf:/var/www/html/pdf barcode-generator
