<template>
    <el-card>
        ini nanti dasboard
    </el-card>
</template>

<script>
export default {
    mounted() {
        this.$store.commit('getNavigationList')
    }
}
</script>
