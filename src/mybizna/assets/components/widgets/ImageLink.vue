<template>
    <div class="card card-coin">
        <div class="card-body text-center image-link p-2">
            <a :href="url" :title="title" class="text-center text-decoration-none">
                <h2 :class="'border rounded-circle m-2 mt-0 ' + class_str">
                    <i :class="icon"></i>
                </h2>

                <p class="text-black">{{ title }}</p>
            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        icon: String,
        title: String,
        class_str: String,
        image: String,
        url: String,
    },
};
</script>

<style>
.image-link {
    margin: 0 auto;
}

.image-link h2 {
    height: 64px;
    width: 64px;
    line-height: 64px;
    font-size: 36px;
}
</style>
