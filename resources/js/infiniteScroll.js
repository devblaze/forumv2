window.infiniteScroll = infiniteScroll;

function infiniteScroll({ apiUrl, dataField }) {
    return {
        items: [],
        page: 1,
        isLoading: false,
        hasMore: true,

        async init() {
            // Load the initial batch
            await this.loadData();

            // Infinite scroll handler
            window.addEventListener('scroll', async () => {
                if (
                    window.innerHeight + window.scrollY >= document.body.offsetHeight - 200 &&
                    this.hasMore &&
                    !this.isLoading
                ) {
                    await this.loadData();
                }
            });
        },

        async loadData() {
            if (!this.hasMore || this.isLoading) return;
            this.isLoading = true;

            try {
                const response = await fetch(`${apiUrl}?page=${this.page}`);
                const data = await response.json();

                // In case "dataField" doesn't exist or is empty
                const newPosts = data[dataField] ?? [];

                // Filter out any duplicates that may already be in items
                const uniqueNewPosts = newPosts.filter(
                    newPost => !this.items.some(existingPost => existingPost.id === newPost.id)
                );

                // If no new items after filtering, we assume we’re at the end
                if (uniqueNewPosts.length === 0) {
                    this.hasMore = false;
                } else {
                    this.items.push(...uniqueNewPosts);
                    this.page++;
                }
            } catch (error) {
                console.error('Error loading data:', error);
            } finally {
                this.isLoading = false;
            }
        }
    };
}
