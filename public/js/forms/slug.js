let slugify = (text) =>
    text
        .toString()
        .toLowerCase()
        .trim()
        .normalize("NFD") // separate accent from letter
        .replace(/[\u0300-\u036f]/g, "") // remove all separated accents
        .replace(/\s+/g, "-") // replace spaces with -
        .replace(/[^\w\-]+/g, "") // remove all non-word chars
        .replace(/\-\-+/g, "-"); // replace multiple '-' with single '-'

let randString = (Math.random() + 1).toString(36).substring(5);
crud.field("title").onChange((field) => {
    crud.field("slug").input.value = slugify(field.value)+'-'+randString;
});

crud.field("name").onChange((field) => {
    crud.field("slug").input.value = slugify(field.value);
});
