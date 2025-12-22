#!/bin/bash

# Script untuk generate PDF dari dokumentasi markdown
# Requirements: pandoc dan xelatex (atau wkhtmltopdf)

echo "🚀 Generating PDF Documentation..."

# Check if pandoc is installed
if ! command -v pandoc &> /dev/null; then
    echo "❌ Pandoc tidak ditemukan. Silakan install terlebih dahulu:"
    echo "   macOS: brew install pandoc"
    echo "   Ubuntu: sudo apt-get install pandoc"
    exit 1
fi

# Check if xelatex is installed (for better PDF rendering)
if command -v xelatex &> /dev/null; then
    PDF_ENGINE="xelatex"
    echo "✅ Menggunakan XeLaTeX untuk rendering PDF"
else
    PDF_ENGINE="pdflatex"
    echo "⚠️  XeLaTeX tidak ditemukan, menggunakan pdflatex"
fi

# Generate PDF from markdown
pandoc FEATURES-DOCUMENTATION.md \
    -o FEATURES-DOCUMENTATION.pdf \
    --pdf-engine=$PDF_ENGINE \
    -V geometry:margin=1in \
    -V fontsize=11pt \
    -V documentclass=article \
    --toc \
    --toc-depth=3 \
    --highlight-style=tango \
    -f markdown+hard_line_breaks \
    --metadata title="ISP Billing Management System - Dokumentasi Fitur" \
    --metadata author="ISP Billing Team" \
    --metadata date="$(date +'%Y-%m-%d')"

if [ $? -eq 0 ]; then
    echo "✅ PDF berhasil di-generate: FEATURES-DOCUMENTATION.pdf"
    echo "📄 File size: $(du -h FEATURES-DOCUMENTATION.pdf | cut -f1)"
else
    echo "❌ Gagal generate PDF. Pastikan pandoc dan LaTeX sudah terinstall."
    exit 1
fi

