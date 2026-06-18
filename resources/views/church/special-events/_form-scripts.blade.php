<script>
    (function () {
        var categorySelect = document.getElementById('category');
        var otherGroup = document.getElementById('categoryOtherGroup');
        var otherInput = document.getElementById('category_other');

        function toggleCategoryOther() {
            var isOther = categorySelect && categorySelect.value === 'other';
            if (otherGroup) {
                otherGroup.style.display = isOther ? 'block' : 'none';
            }
            if (otherInput) {
                otherInput.required = isOther;
            }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', toggleCategoryOther);
            toggleCategoryOther();
        }
    })();
</script>
