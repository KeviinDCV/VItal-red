import { useState, useEffect } from 'react';
import { Badge } from '@/components/ui/badge';
import { Database, Wifi, WifiOff } from 'lucide-react';

interface DatabaseStatusProps {
    className?: string;
}

export function DatabaseStatus({ className = '' }: DatabaseStatusProps) {
    const [isConnected, setIsConnected] = useState(true);
    const [lastUpdate, setLastUpdate] = useState(new Date());

    useEffect(() => {
        // Verificar conexión cada 5 segundos
        const checkConnection = async () => {
            try {
                const response = await fetch('/health', { method: 'HEAD' });
                setIsConnected(response.ok);
                setLastUpdate(new Date());
            } catch (error) {
                setIsConnected(false);
                setLastUpdate(new Date());
            }
        };

        const interval = setInterval(checkConnection, 5000);
        checkConnection(); // Verificar inmediatamente

        return () => clearInterval(interval);
    }, []);

    return (
        <div className={`flex items-center gap-2 ${className}`}>
            <Database className="w-4 h-4" />
            <Badge 
                variant={isConnected ? "default" : "destructive"}
                className={`flex items-center gap-1 ${isConnected ? 'bg-green-500' : 'bg-red-500'}`}
            >
                {isConnected ? (
                    <>
                        <Wifi className="w-3 h-3" />
                        BD Conectada
                    </>
                ) : (
                    <>
                        <WifiOff className="w-3 h-3" />
                        BD Desconectada
                    </>
                )}
            </Badge>
            <span className="text-xs text-gray-500">
                {lastUpdate.toLocaleTimeString()}
            </span>
        </div>
    );
}