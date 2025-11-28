export default () => ({
    reportData: null,
    isLoading: true,
    filters: {
        start_date: new Date().toISOString().slice(0, 10),
        end_date: new Date().toISOString().slice(0, 10),
        mixer_code: ''
    },

    init() {
        this.fetchReport();
    },

    async fetchReport() {
        this.isLoading = true;
        try {
            const params = new URLSearchParams({
                startDate: this.filters.start_date,
                endDate: this.filters.end_date,
                mixer: this.filters.mixer_code
            });
            const response = await fetch(`/api/reports/production?${params.toString()}`);
            this.reportData = await response.json();
        } catch (error) {
            console.error('Error fetching report:', error);
        } finally {
            this.isLoading = false;
        }
    },

    getMaterialQty(batch, materialCode, storageCode = null) {
        if (!batch.details) return 0;
        const item = batch.details.find(d =>
            d.materialCode === materialCode &&
            (!storageCode || d.storageCode === storageCode)
        );
        return item ? item.quantity : 0;
    }
});
