const fileInput = document.querySelector('#product-images');

if (fileInput) {
    const preview = document.querySelector('#product-image-preview');
    const status = document.querySelector('#product-image-status');
    const formats = new Set(['image/jpeg', 'image/png', 'image/webp']);
    let objectUrls = [];

    fileInput.addEventListener('change', () => {
        objectUrls.forEach(url => URL.revokeObjectURL(url));
        objectUrls = [];
        preview.replaceChildren();
        status.textContent = '';
        fileInput.setCustomValidity('');
        const files = Array.from(fileInput.files);
        const slots = Number(fileInput.dataset.imageSlots);

        if (files.length > slots || files.some(file => file.size > 2 * 1024 * 1024 || (file.type && !formats.has(file.type)))) {
            status.textContent = `Pilih hingga ${slots} file JPEG, PNG, atau WebP, maksimal 2 MB per file.`;
            fileInput.setCustomValidity(status.textContent);
            return;
        }

        files.forEach(file => {
            const url = URL.createObjectURL(file);
            objectUrls.push(url);
            const item = document.createElement('li');
            item.className = 'product-gallery-card';
            const image = document.createElement('img');
            image.className = 'product-gallery-photo';
            image.alt = '';
            image.src = url;
            const name = document.createElement('span');
            name.className = 'd-block p-2 small text-break';
            name.textContent = file.name;
            item.append(image, name);
            preview.append(item);
        });
        if (files.length > 0) {
            status.textContent = `${files.length} gambar siap disimpan.`;
        }
    });
}
