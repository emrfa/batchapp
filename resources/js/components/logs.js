export default () => ({
    logs: [],
    pagination: {},
    filters: {
        startDate: '',
        endDate: '',
        search: '',
        page: 1
    },
    isLoading: true,

    init() {
        // Initialize filters (if needed)
        const urlParams = new URLSearchParams(window.location.search);
        this.filters.startDate = urlParams.get('startDate') || '';
        this.filters.endDate = urlParams.get('endDate') || '';
        this.filters.search = urlParams.get('search') || '';

        // Initialize Flatpickr
        this.$nextTick(() => {
            flatpickr(this.$refs.dateRangePicker, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                defaultDate: [this.filters.startDate, this.filters.endDate],
                onChange: (selectedDates, dateStr, instance) => {
                    if (selectedDates.length === 2) {
                        this.filters.startDate = instance.formatDate(selectedDates[0], 'Y-m-d');
                        this.filters.endDate = instance.formatDate(selectedDates[1], 'Y-m-d');
                        this.fetchLogs(1);
                    }
                }
            });
        });

        this.fetchLogs();
    },

    async fetchLogs(page = 1) {
        this.isLoading = true;
        this.filters.page = page;

        const params = new URLSearchParams(this.filters);

        try {
            const response = await fetch(`/api/logs?${params.toString()}`);
            const data = await response.json();
            this.logs = data.data;
            this.pagination = {
                current_page: data.current_page,
                last_page: data.last_page,
                prev_page_url: data.prev_page_url,
                next_page_url: data.next_page_url,
                links: data.links
            };

            // Update URL without reloading
            window.history.pushState({}, '', `?${params.toString()}`);
        } catch (error) {
            console.error('Error fetching logs:', error);
        } finally {
            this.isLoading = false;
        }
    },

    resetFilters() {
        this.filters.startDate = '';
        this.filters.endDate = '';
        this.filters.search = '';


        if (this.$refs.dateRangePicker && this.$refs.dateRangePicker._flatpickr) {
            this.$refs.dateRangePicker._flatpickr.clear();
        }

        this.fetchLogs(1);
    }
});
