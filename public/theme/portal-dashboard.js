document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined' || !window.portalChart) {
        return;
    }

    var el = document.querySelector('#portal-main-chart');
    if (!el) {
        return;
    }

    var cfg = window.portalChart;
    var fmt = new Intl.NumberFormat('fr-FR');

    if (cfg.mode === 'compare' && cfg.compare) {
        var cmp = cfg.compare;
        var series = (cmp.series || [])
            .filter(function (s) { return Array.isArray(s.data); })
            .map(function (s) {
                return { name: s.name || 'Série', data: s.data };
            });

        if (series.length === 0 || !(cmp.labels || []).length) {
            el.innerHTML = '<p class="text-muted text-center py-5">Données insuffisantes pour la comparaison.</p>';
            return;
        }

        new ApexCharts(el, {
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true, tools: { download: true, zoom: true, reset: true } },
                zoom: { enabled: true },
            },
            stroke: { width: 3, curve: 'smooth' },
            markers: { size: 4, hover: { size: 6 } },
            colors: ['#10149a', '#f89f4d'],
            series: series,
            xaxis: {
                categories: cmp.labels || [],
                title: { text: cmp.subtitle || 'Jours relatifs', style: { fontSize: '11px' } },
            },
            yaxis: {
                labels: {
                    formatter: function (v) {
                        return v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? Math.round(v / 1000) + 'k' : Math.round(v);
                    },
                },
                title: { text: 'Volume XOF' },
            },
            legend: { position: 'top', horizontalAlign: 'left' },
            grid: { strokeDashArray: 4 },
            tooltip: {
                shared: true,
                y: { formatter: function (v) { return fmt.format(v) + ' XOF'; } },
            },
        }).render();

        return;
    }

    var single = cfg.single || {};
    if (!single.labels || single.labels.length === 0) {
        el.innerHTML = '<p class="text-muted text-center py-5">Aucune transaction réussie sur cette période.</p>';
        return;
    }

    var hasCounts = Array.isArray(single.success_count)
        && single.success_count.some(function (v) { return v > 0; });

    var chartSeries = hasCounts
        ? [
            { name: 'Net encaissé', type: 'area', data: single.volume || [] },
            { name: 'Payins réussis', type: 'line', data: single.success_count || [] },
        ]
        : [{ name: 'Net encaissé', type: 'area', data: single.volume || [] }];

    var chartOptions = {
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: true, tools: { download: true, zoom: true, reset: true } },
            zoom: { enabled: true },
        },
        stroke: hasCounts ? { width: [3, 2], curve: 'smooth' } : { width: 3, curve: 'smooth' },
        fill: hasCounts
            ? {
                type: ['gradient', 'solid'],
                gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] },
                opacity: [1, 0],
            }
            : {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] },
            },
        colors: ['#10149a', '#f89f4d'],
        series: chartSeries,
        xaxis: { categories: single.labels || [] },
        yaxis: hasCounts
            ? [
                {
                    labels: {
                        formatter: function (v) {
                            return v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? Math.round(v / 1000) + 'k' : Math.round(v);
                        },
                    },
                    title: { text: 'Volume XOF' },
                },
                {
                    opposite: true,
                    labels: { formatter: function (v) { return Math.round(v); } },
                    title: { text: 'Transactions' },
                },
            ]
            : {
                labels: {
                    formatter: function (v) {
                        return v >= 1000000 ? (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? Math.round(v / 1000) + 'k' : Math.round(v);
                    },
                },
                title: { text: 'Volume XOF' },
            },
        dataLabels: { enabled: false },
        grid: { strokeDashArray: 4 },
        legend: { position: 'top', horizontalAlign: 'left' },
        tooltip: {
            shared: true,
            y: hasCounts
                ? [
                    { formatter: function (v) { return fmt.format(v) + ' XOF'; } },
                    { formatter: function (v) { return Math.round(v) + ' tx'; } },
                ]
                : { formatter: function (v) { return fmt.format(v) + ' XOF'; } },
        },
    };

    new ApexCharts(el, chartOptions).render();
});
