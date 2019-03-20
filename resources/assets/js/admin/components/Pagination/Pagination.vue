<template>
    <nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">
        <a class="pagination-previous" href="#" @click.prevent="changePage(1)" :disabled="pagination.current_page <= 1"><<</a>
        <a class="pagination-previous" href="#" @click.prevent="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"><</a>
        <a class="pagination-next" href="#" @click.prevent="changePage(pagination.current_page + 1)"
           :disabled="pagination.current_page >= pagination.last_page">></a>
        <a class="pagination-next" href="#" @click.prevent="changePage(pagination.last_page)" :disabled="pagination.current_page >= pagination.last_page">>></a>
        <ul class="pagination-list">
            <li v-for="page in pages">
                <a class="pagination-link" :class="isCurrentPage(page) ? 'is-current' : ''" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
        </ul>
    </nav>
</template>

<script>
    import './Pagination.css';

    export default {
        props:
            ['pagination', 'offset'],

        data() {
            return {
                posts: {},
                pagination: {
                    'current_page': 1,
                    'total': 0,
                }
            }
        },

        computed: {
            pages() {
                let pages = [];
                let from = this.pagination.current_page - Math.floor(this.offset / 2);
                if (from >= (this.pagination.last_page - 1)) {
                    from--;
                }
                if (from < 1) {
                    from = 1;
                }
                let to = from + this.offset - 1;
                if (to > this.pagination.last_page) {
                    to = this.pagination.last_page;
                }
                while (from <= to) {
                    pages.push(from);
                    from++;
                }
                return pages;
            }
        },

        methods: {
            isCurrentPage(page) {
                return this.pagination.current_page === page;
            },

            changePage(page) {
                if (page > this.pagination.last_page) {
                    page = this.pagination.last_page;
                }
                this.pagination.current_page = page;
                this.$emit('paginate', page);
            },
        },
    }
</script>