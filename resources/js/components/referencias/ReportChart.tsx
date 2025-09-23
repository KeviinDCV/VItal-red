import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3, TrendingUp, PieChart } from 'lucide-react';

interface ChartData {
    labels: string[];
    data: number[];
    colors?: string[];
}

interface ReportChartProps {
    title: string;
    type: 'bar' | 'line' | 'pie';
    data: ChartData;
    height?: number;
    showLegend?: boolean;
}

export function ReportChart({ title, type, data, height = 300, showLegend = true }: ReportChartProps) {
    const getIcon = () => {
        switch (type) {
            case 'bar': return <BarChart3 className="h-4 w-4" />;
            case 'line': return <TrendingUp className="h-4 w-4" />;
            case 'pie': return <PieChart className="h-4 w-4" />;
        }
    };

    const maxValue = Math.max(...data.data);
    const colors = data.colors || ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'];

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    {getIcon()}
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {type === 'bar' && (
                        <div className="space-y-3" style={{ height }}>
                            {data.labels.map((label, index) => (
                                <div key={label} className="flex items-center gap-3">
                                    <div className="w-24 text-sm text-right">{label}</div>
                                    <div className="flex-1 bg-gray-200 rounded-full h-6 relative">
                                        <div 
                                            className="h-6 rounded-full flex items-center justify-end pr-2 text-white text-xs font-medium"
                                            style={{ 
                                                width: `${(data.data[index] / maxValue) * 100}%`,
                                                backgroundColor: colors[index % colors.length]
                                            }}
                                        >
                                            {data.data[index]}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    {type === 'pie' && (
                        <div className="flex items-center justify-center" style={{ height }}>
                            <div className="relative">
                                <svg width="200" height="200" viewBox="0 0 200 200">
                                    {data.data.map((value, index) => {
                                        const total = data.data.reduce((sum, val) => sum + val, 0);
                                        const percentage = (value / total) * 100;
                                        const angle = (percentage / 100) * 360;
                                        const startAngle = data.data.slice(0, index).reduce((sum, val) => sum + (val / total) * 360, 0);
                                        
                                        const x1 = 100 + 80 * Math.cos((startAngle - 90) * Math.PI / 180);
                                        const y1 = 100 + 80 * Math.sin((startAngle - 90) * Math.PI / 180);
                                        const x2 = 100 + 80 * Math.cos((startAngle + angle - 90) * Math.PI / 180);
                                        const y2 = 100 + 80 * Math.sin((startAngle + angle - 90) * Math.PI / 180);
                                        
                                        const largeArcFlag = angle > 180 ? 1 : 0;
                                        
                                        return (
                                            <path
                                                key={index}
                                                d={`M 100 100 L ${x1} ${y1} A 80 80 0 ${largeArcFlag} 1 ${x2} ${y2} Z`}
                                                fill={colors[index % colors.length]}
                                                stroke="white"
                                                strokeWidth="2"
                                            />
                                        );
                                    })}
                                </svg>
                            </div>
                        </div>
                    )}

                    {showLegend && (
                        <div className="flex flex-wrap gap-4 justify-center">
                            {data.labels.map((label, index) => (
                                <div key={label} className="flex items-center gap-2">
                                    <div 
                                        className="w-3 h-3 rounded-full"
                                        style={{ backgroundColor: colors[index % colors.length] }}
                                    />
                                    <span className="text-sm">{label}</span>
                                    <span className="text-sm font-medium">({data.data[index]})</span>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}