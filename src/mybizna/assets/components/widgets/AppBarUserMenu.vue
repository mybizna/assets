<template>
    <div class="dropdown">
        <v-badge
            color="success"
            id="dropdownMenuLink"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            dot
        >
            <v-avatar size="28px">
                <v-img src="images/avatars/1.png"></v-img>
            </v-avatar>
            <div style="line-height: 28px">{{ $store.state.auth.user.username }}</div>
        </v-badge>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <li>
                <a class="dropdown-item" href="https://mybizna.com">Help</a>
            </li>
            <li>
                <router-link class="dropdown-item" :to="'/profile'">Profile</router-link>
            </li>
            <li>
                <a class="dropdown-item" href="#" @click="logout()">Logout</a>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    setup() {
        return {};
    },

    methods: {
        logout() {
            this.$store.commit("auth/logout");
        },
    },
};
</script>

<style lang="scss">
.user-profile-menu-content {
    .v-list-item {
        min-height: 2.5rem !important;
    }
}
</style>
