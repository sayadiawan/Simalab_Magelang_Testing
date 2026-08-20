/**
 * Simple Auto Replace Script (JavaScript)
 * Jalankan di browser console saat membuka index.html
 * 
 * Cara pakai:
 * 1. Buka index.html di browser
 * 2. Buka Developer Tools (F12)
 * 3. Paste script ini di Console
 * 4. Tekan Enter
 */

(function() {
    console.log('🚀 Starting Auto Replace Screenshot...');
    
    // Mapping screenshot ke section
    const mapping = {
        'master-data-customer': ['customer'],
        'master-data-sample-type': ['sample-type', 'sampletype'],
        'master-data-laboratorium': ['laboratorium', 'lab'],
        'master-data-method': ['method'],
        'master-data-packet': ['packet', 'paket'],
        'master-data-unit': ['unit'],
        'master-data-container': ['container', 'wadah'],
        'master-data-pasien': ['pasien', 'patient'],
        'permohonan-uji-create': ['permohonan-uji', 'permohonanuji'],
        'sample-add': ['sample-form', 'sample-add'],
        'sample-receive': ['sample-receive', 'sample-receive-form'],
        'klinik-step1': ['klinik-step1', 'klinik-1', 'step1'],
        'klinik-step2': ['klinik-step2', 'klinik-2', 'step2'],
        'klinik-step3': ['klinik-step3', 'klinik-3', 'step3'],
        'klinik-parameter': ['klinik-parameter', 'parameter']
    };
    
    // Get all placeholders
    const placeholders = document.querySelectorAll('.screenshot-placeholder');
    let replaced = 0;
    
    placeholders.forEach(placeholder => {
        // Find parent section
        const section = placeholder.closest('.doc-section');
        if (!section) return;
        
        const sectionId = section.id;
        if (!sectionId || !mapping[sectionId]) return;
        
        // Get screenshot list (you need to provide this)
        // For now, we'll use a simple pattern match
        const keywords = mapping[sectionId];
        
        // Create img tag (you need to provide actual screenshot filename)
        // This is a template - you need to modify based on your actual files
        const screenshotName = keywords[0] + '-list.png'; // Default pattern
        
        // Replace placeholder
        const container = placeholder.parentElement;
        const img = document.createElement('img');
        img.src = `screenshots/${screenshotName}`;
        img.alt = sectionId.replace(/-/g, ' ');
        img.onerror = function() {
            console.warn(`⚠️ Screenshot not found: ${screenshotName}`);
            this.style.display = 'none';
        };
        
        container.innerHTML = '';
        container.appendChild(img);
        replaced++;
        
        console.log(`✅ Replaced: ${sectionId} → ${screenshotName}`);
    });
    
    console.log(`🎉 Done! Replaced ${replaced} placeholders.`);
    console.log('💡 Note: Make sure screenshot files exist in screenshots/ folder');
})();

