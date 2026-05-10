/**
 * Lazy-loaded ApexCharts wrapper for the admin Analytics tab. Kept in its
 * own file so Vite emits a separate chunk that only loads when the tab is
 * opened. Mirrors the same pattern as codemirror-css.js.
 *
 * Each renderer returns the chart instance so the caller can destroy() it
 * if the host element is removed.
 */
import ApexCharts from 'apexcharts';

const baseTheme = {
    chart: {
        background: 'transparent',
        foreColor: 'rgba(255, 255, 255, 0.7)',
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    grid: {
        borderColor: 'rgba(255, 255, 255, 0.06)',
        strokeDashArray: 3,
    },
    legend: {
        labels: { colors: 'rgba(255, 255, 255, 0.7)' },
    },
};

export function renderDonut(host, { labels, values, colors }) {
    const chart = new ApexCharts(host, {
        ...baseTheme,
        chart: { ...baseTheme.chart, type: 'donut', height: 220 },
        series: values,
        labels,
        colors: colors ?? ['#10b981', '#fb7185', '#f59e0b'],
        stroke: { width: 2, colors: ['rgba(15, 17, 23, 0.95)'] },
        legend: {
            ...baseTheme.legend,
            position: 'bottom',
            fontSize: '11px',
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            color: 'rgba(255,255,255,0.7)',
                            fontSize: '11px',
                        },
                        value: { color: '#fff', fontSize: '20px', fontWeight: 700 },
                    },
                },
            },
        },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' },
    });
    chart.render();
    return chart;
}

export function renderBar(host, { categories, values, color }) {
    const chart = new ApexCharts(host, {
        ...baseTheme,
        chart: { ...baseTheme.chart, type: 'bar', height: 220 },
        series: [{ name: 'Opens', data: values }],
        xaxis: {
            categories,
            labels: { style: { colors: 'rgba(255,255,255,0.55)', fontSize: '10px' } },
            axisBorder: { color: 'rgba(255,255,255,0.08)' },
            axisTicks: { color: 'rgba(255,255,255,0.08)' },
        },
        yaxis: {
            labels: { style: { colors: 'rgba(255,255,255,0.55)', fontSize: '10px' } },
        },
        colors: [color ?? '#10b981'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '60%',
            },
        },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' },
    });
    chart.render();
    return chart;
}
