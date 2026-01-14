/**
 * Topic Evolution Chart - Sankey-style ribbon diagram
 * Reusable function for visualizing topic evolution over time
 */

(function() {
    'use strict';
    
    // Module namespace
    const TopicEvolutionChart = {};
    
    // Helper function to create ribbon path between two nodes (Sankey-style)
    function createRibbonPath(x1, y1_top, y1_bottom, x2, y2_top, y2_bottom) {
        const dx = x2 - x1;
        const curve = Math.min(dx * 0.4, 50); // Curvature factor, max 50px
        
        // Create smooth curved path connecting top and bottom edges
        return 'M' + x1 + ',' + y1_top +
               'C' + (x1 + curve) + ',' + y1_top + ' ' + (x2 - curve) + ',' + y2_top + ' ' + x2 + ',' + y2_top +
               'L' + x2 + ',' + y2_bottom +
               'C' + (x2 - curve) + ',' + y2_bottom + ' ' + (x1 + curve) + ',' + y1_bottom + ' ' + x1 + ',' + y1_bottom +
               'Z';
    }
    
    // RGB to HSL conversion
    function rgbToHsl(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;
        
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h = 0;
        let s = 0;
        const l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }
        
        return { h: h * 360, s: s, l: l };
    }
    
    // HSL to RGB conversion
    function hslToRgb(h, s, l) {
        h /= 360;
        let r, g, b;
        
        if (s === 0) {
            r = g = b = l;
        } else {
            const hue2rgb = function(p, q, t) {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };
            
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }
        
        return {
            r: Math.round(r * 255),
            g: Math.round(g * 255),
            b: Math.round(b * 255)
        };
    }
    
    // Generate flowing ribbon colors from theme color
    function generateRibbonColors(baseColor, count) {
        const colors = [];
        baseColor = baseColor.replace('#', '');
        
        let r = parseInt(baseColor.substr(0, 2), 16);
        let g = parseInt(baseColor.substr(2, 2), 16);
        let b = parseInt(baseColor.substr(4, 2), 16);
        
        const hueShift = 30;
        const saturationVariation = 0.2;
        const lightnessVariation = 0.15;
        
        for (let i = 0; i < count; i++) {
            const hueShiftAmount = (i * hueShift) % 360;
            const opacity = 0.65 + (i * 0.08);
            const lightness = 0.5 + (i * lightnessVariation);
            
            const hsl = rgbToHsl(r, g, b);
            let newHue = (hsl.h + hueShiftAmount) % 360;
            let newSat = Math.min(1, Math.max(0.3, hsl.s + (i * saturationVariation)));
            let newLight = Math.min(0.9, Math.max(0.3, lightness));
            
            const rgb = hslToRgb(newHue, newSat, newLight);
            
            colors.push('rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + opacity + ')');
        }
        
        return colors;
    }
    
    // Shared tooltip for all diagrams
    let tooltip = null;
    function getTooltip() {
        if (!tooltip) {
            let tooltipEl = d3.select('body').select('.topics-tooltip');
            if (tooltipEl.empty()) {
                tooltipEl = d3.select('body').append('div')
                    .attr('class', 'topics-tooltip')
                    .style('opacity', 0)
                    .style('position', 'absolute')
                    .style('background', 'rgba(0, 0, 0, 0.9)')
                    .style('color', 'white')
                    .style('padding', '10px')
                    .style('border-radius', '4px')
                    .style('pointer-events', 'none')
                    .style('font-size', '12px')
                    .style('z-index', '10000')
                    .style('display', 'block')
                    .style('visibility', 'visible');
            }
            tooltip = tooltipEl;
        }
        return tooltip;
    }

    /**
     * Create a topic evolution chart
     * @param {Object} options Configuration object
     * @param {string} options.containerId - ID of the container element
     * @param {Array} options.data - Array of {year, topic, count} objects
     * @param {Array} options.years - Array of years
     * @param {Array} options.topics - Array of topic names
     * @param {Array} options.baseColors - Array of base colors
     * @param {string} options.yAxisLabel - Label for y-axis
     * @param {string} options.valueLabel - Label for values (e.g., 'papers', 'citations')
     * @param {number} options.height - Chart height (default: 500)
     * @param {boolean} options.showLegend - Whether to show legend (default: false)
     */
    TopicEvolutionChart.render = function(options) {
        const containerId = options.containerId;
        const data = options.data;
        const years = options.years;
        const topics = options.topics;
        const baseColors = options.baseColors;
        const yAxisLabel = options.yAxisLabel || 'Count';
        const valueLabel = options.valueLabel || 'items';
        const height = options.height || 500;
        const showLegend = options.showLegend || false;
        
        const chartColor = getComputedStyle(document.documentElement).getPropertyValue('--main-color');
        
        // Generate colors from theme if available
        let colors = {};
        if (chartColor) {
            const colorArray = generateRibbonColors(chartColor, topics.length);
            topics.forEach(function(topic, i) {
                colors[topic] = colorArray[i];
            });
        } else {
            topics.forEach(function(topic, i) {
                colors[topic] = baseColors[i];
            });
        }
        
        // Set up dimensions
        const margin = { top: 20, right: 30, bottom: showLegend ? 100 : 60, left: 60 };
        const container = d3.select('#' + containerId);
        const containerNode = container.node();
        if (!containerNode) {
            console.error('Container not found: ' + containerId);
            return;
        }
        const width = containerNode.clientWidth - margin.left - margin.right;
        const chartHeight = height - margin.top - margin.bottom;
        
        // Create SVG
        const svgHeight = showLegend ? chartHeight + margin.top + margin.bottom + 100 : chartHeight + margin.top + margin.bottom + 20;
        const svg = container
            .append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', svgHeight)
            .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');
        
        // Calculate node positions for each year
        const nodeWidth = 30;
        const yearSpacing = years.length > 1 ? (width - nodeWidth) / (years.length - 1) : 0;
        
        // Find maximum total count across all years for scaling
        const maxTotal = d3.max(years.map(function(y) {
            return d3.sum(data.filter(function(d) { return d.year === y; }), function(d) { return d.count; });
        }));
        
        // Calculate node positions for each year
        const segmentGap = 8;
        const yearNodes = {};
        
        years.forEach(function(year) {
            yearNodes[year] = {};
            
            const yearData = data.filter(function(d) { return d.year === year; });
            const sortedYearData = yearData.slice().sort(function(a, b) { return b.count - a.count; });
            
            let cumulative = 0;
            sortedYearData.forEach(function(d) {
                yearNodes[year][d.topic] = {
                    top: cumulative,
                    bottom: cumulative + d.count,
                    count: d.count
                };
                cumulative += d.count;
            });
        });
        
        // Scale all years proportionally to fit height, accounting for gaps
        const totalGaps = (topics.length - 1) * segmentGap;
        const scale = maxTotal > 0 ? (chartHeight - totalGaps) / maxTotal : 1;
        
        years.forEach(function(year) {
            const sortedTopics = Object.keys(yearNodes[year]).sort(function(a, b) {
                return yearNodes[year][b].count - yearNodes[year][a].count;
            });
            
            let gapOffset = 0;
            sortedTopics.forEach(function(topic, index) {
                const node = yearNodes[year][topic];
                const scaledTop = node.top * scale;
                const scaledBottom = node.bottom * scale;
                
                if (index > 0) {
                    gapOffset += segmentGap;
                }
                
                node.top = scaledTop + gapOffset;
                node.bottom = scaledBottom + gapOffset;
            });
        });
        
        // Use shared tooltip
        const tooltip = getTooltip();
    
    // Draw ribbons (flows) between consecutive years
    for (let i = 0; i < years.length - 1; i++) {
        const year1 = years[i];
        const year2 = years[i + 1];
        const x1 = i * yearSpacing + nodeWidth / 2;
        const x2 = (i + 1) * yearSpacing + nodeWidth / 2;
        
        topics.forEach(function(topic) {
            const node1 = yearNodes[year1][topic];
            const node2 = yearNodes[year2][topic];
            
            const count1 = node1 && node1.count > 0 ? node1.count : 0;
            const count2 = node2 && node2.count > 0 ? node2.count : 0;
            
            // Skip if both counts are zero
            if (count1 === 0 && count2 === 0) return;
            
            // Determine positions
            let y1_top, y1_bottom, y2_top, y2_bottom;
            
            if (count1 > 0 && count2 > 0) {
                // Both have counts - draw normal ribbon
                y1_top = node1.top;
                y1_bottom = node1.bottom;
                y2_top = node2.top;
                y2_bottom = node2.bottom;
            } else {
                // One or both are zero - draw a line connecting centers
                let y1_center, y2_center;
                
                if (count1 > 0) {
                    y1_center = (node1.top + node1.bottom) / 2;
                } else {
                    const year1Topics = Object.keys(yearNodes[year1]).filter(t => yearNodes[year1][t].count > 0);
                    if (year1Topics.length > 0) {
                        const avgTop = d3.mean(year1Topics.map(t => yearNodes[year1][t].top));
                        const avgBottom = d3.mean(year1Topics.map(t => yearNodes[year1][t].bottom));
                        y1_center = (avgTop + avgBottom) / 2;
                    } else {
                        y1_center = chartHeight / 2;
                    }
                }
                
                if (count2 > 0) {
                    y2_center = (node2.top + node2.bottom) / 2;
                } else {
                    const year2Topics = Object.keys(yearNodes[year2]).filter(t => yearNodes[year2][t].count > 0);
                    if (year2Topics.length > 0) {
                        const avgTop = d3.mean(year2Topics.map(t => yearNodes[year2][t].top));
                        const avgBottom = d3.mean(year2Topics.map(t => yearNodes[year2][t].bottom));
                        y2_center = (avgTop + avgBottom) / 2;
                    } else {
                        y2_center = chartHeight / 2;
                    }
                }
                
                // Draw a thin line connecting the centers
                const lineThickness = 1;
                y1_top = y1_center - lineThickness / 2;
                y1_bottom = y1_center + lineThickness / 2;
                y2_top = y2_center - lineThickness / 2;
                y2_bottom = y2_center + lineThickness / 2;
            }
            
            // Create ribbon path (or line path)
            const ribbonPath = createRibbonPath(x1, y1_top, y1_bottom, x2, y2_top, y2_bottom);
            
            svg.append('path')
                .datum({topic: topic, year1: year1, year2: year2})
                .attr('d', ribbonPath)
                .attr('fill', count1 > 0 && count2 > 0 ? colors[topic] : 'none')
                .attr('stroke', colors[topic])
                .attr('stroke-width', count1 > 0 && count2 > 0 ? 0 : 2)
                .attr('stroke-dasharray', 'none')
                .style('opacity', count1 > 0 && count2 > 0 ? 0.7 : 0.5)
                .on('mouseenter', function(d) {
                    if (!d) return;
                    const topicName = d.topic;
                    const c1 = yearNodes[d.year1][topicName] ? yearNodes[d.year1][topicName].count : 0;
                    const c2 = yearNodes[d.year2][topicName] ? yearNodes[d.year2][topicName].count : 0;
                    d3.select(this).transition().duration(100).style('opacity', c1 > 0 && c2 > 0 ? 1 : 0.8);
                })
                .on('mousemove', function(d) {
                    if (!d) return;
                    const topicName = d.topic;
                    const count1 = yearNodes[d.year1][topicName] ? yearNodes[d.year1][topicName].count : 0;
                    const count2 = yearNodes[d.year2][topicName] ? yearNodes[d.year2][topicName].count : 0;
                    
                    const mouseEvent = d3.event || window.event;
                    const pageX = mouseEvent.pageX || mouseEvent.clientX;
                    const pageY = mouseEvent.pageY || mouseEvent.clientY;
                    
                    tooltip
                        .style('opacity', 1)
                        .style('display', 'block')
                        .style('visibility', 'visible')
                        .style('left', (pageX + 10) + 'px')
                        .style('top', (pageY - 10) + 'px')
                        .html('<strong>Topic: ' + topicName + '</strong><br/>' +
                              '<strong>Years: ' + d.year1 + ' → ' + d.year2 + '</strong><br/>' +
                              d.year1 + ': ' + count1.toLocaleString() + ' ' + valueLabel + '<br/>' +
                              d.year2 + ': ' + count2.toLocaleString() + ' ' + valueLabel);
                })
                .on('mouseleave', function(d) {
                    if (!d) return;
                    const topicName = d.topic;
                    const count1 = yearNodes[d.year1][topicName] ? yearNodes[d.year1][topicName].count : 0;
                    const count2 = yearNodes[d.year2][topicName] ? yearNodes[d.year2][topicName].count : 0;
                    d3.select(this).transition().duration(100).style('opacity', count1 > 0 && count2 > 0 ? 0.7 : 0.5);
                    tooltip
                        .style('opacity', 0)
                        .style('display', 'none');
                });
        });
    }
    
    // Add data point labels (counts) at each year position
    years.forEach(function(year, i) {
        const x = i * yearSpacing + nodeWidth / 2;
        
        topics.forEach(function(topic) {
            const node = yearNodes[year][topic];
            if (!node || node.count === 0) return;
            
            const segmentCenterY = (node.top + node.bottom) / 2;
            
            svg.append('text')
                .attr('x', x)
                .attr('y', segmentCenterY)
                .attr('text-anchor', 'middle')
                .attr('dy', '0.35em')
                .style('font-size', '10px')
                .style('fill', '#333')
                .style('font-weight', 'bold')
                .style('pointer-events', 'none')
                .text(node.count.toLocaleString());
        });
    });
    
    // Add y-axis label
    svg.append('text')
        .attr('transform', 'rotate(-90)')
        .attr('y', -margin.left + 20)
        .attr('x', -chartHeight / 2)
        .attr('text-anchor', 'middle')
        .style('font-size', '14px')
        .style('font-weight', 'bold')
        .text(yAxisLabel);
    
    // Add year labels
    years.forEach(function(year, i) {
        const x = i * yearSpacing + nodeWidth / 2;
        svg.append('text')
            .attr('x', x)
            .attr('y', chartHeight + 20)
            .attr('text-anchor', 'middle')
            .style('font-size', '12px')
            .text(year);
    });
    
    // Add x-axis label
    svg.append('text')
        .attr('x', width / 2)
        .attr('y', chartHeight + 45)
        .attr('text-anchor', 'middle')
        .style('font-size', '14px')
        .style('font-weight', 'bold')
        .text('Publication year');
    
    // Add legend if requested
    if (showLegend) {
        const legendPadding = 10;
        const legendItemSpacing = 20;
        const legendItemHeight = 20;
        const legendRectHeight = legendItemHeight + (legendPadding * 2);
        
        let legendTotalWidth = legendPadding;
        topics.forEach(function(topic) {
            const textWidth = topic.length * 8;
            legendTotalWidth += 15 + 8 + textWidth + legendItemSpacing;
        });
        legendTotalWidth += legendPadding - legendItemSpacing;
        
        const legendStartX = (width - legendTotalWidth) / 2;
        const legendY = chartHeight + 70;
        
        svg.append('rect')
            .attr('x', legendStartX)
            .attr('y', legendY - legendPadding)
            .attr('width', legendTotalWidth)
            .attr('height', legendRectHeight)
            .attr('fill', 'white')
            .attr('stroke', '#ccc')
            .attr('stroke-width', 1)
            .attr('rx', 4);
        
        let currentX = legendStartX + legendPadding;
        const legend = svg.selectAll('.legend')
            .data(topics)
            .enter()
            .append('g')
            .attr('class', 'legend')
            .attr('transform', function(d, i) {
                if (i > 0) {
                    let xPos = legendStartX + legendPadding;
                    for (let j = 0; j < i; j++) {
                        const prevTopic = topics[j];
                        const textWidth = prevTopic.length * 8;
                        xPos += 15 + 8 + textWidth + legendItemSpacing;
                    }
                    return 'translate(' + xPos + ', ' + legendY + ')';
                } else {
                    return 'translate(' + currentX + ', ' + legendY + ')';
                }
            });
        
        const legendItemCenterY = legendItemHeight / 2;
        const colorBoxSize = 15;
        const colorBoxY = (legendItemHeight - colorBoxSize) / 2;
        
        legend.append('rect')
            .attr('width', colorBoxSize)
            .attr('height', colorBoxSize)
            .attr('y', colorBoxY)
            .attr('fill', function(d) { return colors[d]; })
            .attr('stroke', function(d) { return colors[d].replace('0.7', '1.0'); })
            .attr('stroke-width', 0.5);
        
        legend.append('text')
            .attr('x', colorBoxSize + 8)
            .attr('y', legendItemCenterY)
            .attr('dy', '0.35em')
            .style('font-size', '12px')
            .text(function(d) { return d; });
        }
    };
    
    // Export only the main function to global scope
    window.renderTopicEvolutionChart = TopicEvolutionChart.render;
})();
